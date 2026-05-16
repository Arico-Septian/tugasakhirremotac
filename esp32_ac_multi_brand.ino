// ============================================================
//  ESP32 AC Remote Controller — Multi Brand
//  Board   : ESP32
//  Broker  : HiveMQ Cloud (TLS port 8883)
// ============================================================

#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <DHT.h>
#include <IRremoteESP8266.h>
#include <IRsend.h>
#include <ir_Gree.h>
#include <ir_Daikin.h>
#include <ir_Panasonic.h>
#include <ir_LG.h>
#include <ir_Mitsubishi.h>
#include <ir_Sharp.h>
#include <ir_Toshiba.h>
#include <vector>

// ----------------------------------------------------------
//  Konfigurasi
// ----------------------------------------------------------
const char* WIFI_SSID     = "Morrow";
const char* WIFI_PASSWORD = "bawok123";

const char* MQTT_HOST = "4edb07bbc98d48ba9e9154ee9bf84ccd.s1.eu.hivemq.cloud";
const int   MQTT_PORT = 8883;
const char* MQTT_USER = "Arico";
const char* MQTT_PASS = "Arico170903";

#define DEVICE_ID "esp32_01"

#define DHT_PIN  4
#define DHT_TYPE DHT11

// Interval (ms)
const unsigned long PING_INTERVAL    = 15000;
const unsigned long STATUS_INTERVAL  = 10000;
const unsigned long SENSOR_INTERVAL  =  5000;
const unsigned long CONFIG_TIMEOUT   = 10000;

// Reconnect setelah N kali publish gagal berturut-turut (deteksi half-open TCP)
const int MAX_PUBLISH_FAILURES = 3;

// ----------------------------------------------------------
//  Global
// ----------------------------------------------------------
WiFiClientSecure espClient;
PubSubClient     client(espClient);
DHT              dht(DHT_PIN, DHT_TYPE);

String currentRoom    = "";
bool   configReceived = false;
unsigned long startWait = 0;

unsigned long lastStatusPublish = 0;
unsigned long lastSensorPublish = 0;

int consecutivePublishFails = 0;

// ----------------------------------------------------------
//  AC Brand
// ----------------------------------------------------------
enum ACBrand {
  BRAND_GREE,
  BRAND_DAIKIN,
  BRAND_PANASONIC,
  BRAND_LG,
  BRAND_MITSUBISHI,
  BRAND_SHARP,
  BRAND_TOSHIBA
};

String brandName(ACBrand brand) {
  switch (brand) {
    case BRAND_GREE:       return "GREE";
    case BRAND_DAIKIN:     return "DAIKIN";
    case BRAND_PANASONIC:  return "PANASONIC";
    case BRAND_LG:         return "LG";
    case BRAND_MITSUBISHI: return "MITSUBISHI";
    case BRAND_SHARP:      return "SHARP";
    case BRAND_TOSHIBA:    return "TOSHIBA";
    default:               return "UNKNOWN";
  }
}

ACBrand brandFromString(String s) {
  s.toUpperCase();
  if (s == "DAIKIN")     return BRAND_DAIKIN;
  if (s == "PANASONIC")  return BRAND_PANASONIC;
  if (s == "LG")         return BRAND_LG;
  if (s == "MITSUBISHI") return BRAND_MITSUBISHI;
  if (s == "SHARP")      return BRAND_SHARP;
  if (s == "TOSHIBA")    return BRAND_TOSHIBA;
  return BRAND_GREE;
}

// Forward declarations — Arduino IDE auto-generate prototype di awal file,
// jadi struct ACDevice harus dikenali dulu sebelum fungsi yang pakai dia.
struct ACDevice;
void applyMode(ACDevice& ac);
void sendIR(ACDevice& ac);
void publishStatus(ACDevice& ac);

int pinForAcId(int id) {
  switch (id) {
    case  1: return 14;  case  2: return 27;  case  3: return 26;
    case  4: return 25;  case  5: return 33;  case  6: return 32;
    case  7: return 23;  case  8: return 22;  case  9: return 21;
    case 10: return 19;  case 11: return 18;  case 12: return 15;
    case 13: return 13;  case 14: return 12;  case 15: return  5;
    default: return -1;
  }
}

// ----------------------------------------------------------
//  AC Device
// ----------------------------------------------------------
struct ACDevice {
  int    id;
  ACBrand brand;
  bool   power;
  int    setTemp;
  String mode;
  String fanSpeed;
  String swing;
  uint8_t irPin;

  IRGreeAC       gree;
  IRDaikinESP    daikin;
  IRPanasonicAc  panasonic;
  IRLgAc         lg;
  IRMitsubishiAC mitsubishi;
  IRSharpAc      sharp;
  IRToshibaAC    toshiba;

  ACDevice(int _id, ACBrand _brand, uint8_t _pin)
    : id(_id), brand(_brand),
      power(false), setTemp(24),
      mode("AUTO"), fanSpeed("AUTO"), swing("OFF"),
      irPin(_pin),
      gree(_pin), daikin(_pin), panasonic(_pin), lg(_pin),
      mitsubishi(_pin), sharp(_pin), toshiba(_pin)
  {
    gree.begin(); daikin.begin(); panasonic.begin(); lg.begin();
    mitsubishi.begin(); sharp.begin(); toshiba.begin();
  }
};

std::vector<ACDevice> acList;

void addAC(int id, ACBrand brand = BRAND_GREE) {
  for (auto& ac : acList)
    if (ac.id == id) return;

  int pin = pinForAcId(id);
  if (pin == -1) {
    Serial.printf("[AC] ID %d tidak punya mapping pin\n", id);
    return;
  }

  acList.push_back(ACDevice(id, brand, pin));
  Serial.printf("[AC] add | room=%s id=%d brand=%s pin=%d\n",
                currentRoom.c_str(), id, brandName(brand).c_str(), pin);
}

// ----------------------------------------------------------
//  IR Send
// ----------------------------------------------------------
void applyMode(ACDevice& ac) {
  switch (ac.brand) {
    case BRAND_GREE:
      if      (ac.mode == "COOL") ac.gree.setMode(kGreeCool);
      else if (ac.mode == "HEAT") ac.gree.setMode(kGreeHeat);
      else if (ac.mode == "DRY")  ac.gree.setMode(kGreeDry);
      else if (ac.mode == "FAN")  ac.gree.setMode(kGreeFan);
      else                        ac.gree.setMode(kGreeAuto);
      break;
    case BRAND_DAIKIN:
      if      (ac.mode == "COOL") ac.daikin.setMode(kDaikinCool);
      else if (ac.mode == "HEAT") ac.daikin.setMode(kDaikinHeat);
      else if (ac.mode == "DRY")  ac.daikin.setMode(kDaikinDry);
      else if (ac.mode == "FAN")  ac.daikin.setMode(kDaikinFan);
      else                        ac.daikin.setMode(kDaikinAuto);
      break;
    case BRAND_PANASONIC:
      if      (ac.mode == "COOL") ac.panasonic.setMode(kPanasonicAcCool);
      else if (ac.mode == "HEAT") ac.panasonic.setMode(kPanasonicAcHeat);
      else if (ac.mode == "DRY")  ac.panasonic.setMode(kPanasonicAcDry);
      else if (ac.mode == "FAN")  ac.panasonic.setMode(kPanasonicAcFan);
      else                        ac.panasonic.setMode(kPanasonicAcAuto);
      break;
    case BRAND_LG:
      if      (ac.mode == "COOL") ac.lg.setMode(kLgAcCool);
      else if (ac.mode == "HEAT") ac.lg.setMode(kLgAcHeat);
      else if (ac.mode == "DRY")  ac.lg.setMode(kLgAcDry);
      else if (ac.mode == "FAN")  ac.lg.setMode(kLgAcFan);
      else                        ac.lg.setMode(kLgAcAuto);
      break;
    case BRAND_MITSUBISHI:
      if      (ac.mode == "COOL") ac.mitsubishi.setMode(kMitsubishiAcCool);
      else if (ac.mode == "HEAT") ac.mitsubishi.setMode(kMitsubishiAcHeat);
      else if (ac.mode == "DRY")  ac.mitsubishi.setMode(kMitsubishiAcDry);
      else if (ac.mode == "FAN")  ac.mitsubishi.setMode(kMitsubishiAcFan);
      else                        ac.mitsubishi.setMode(kMitsubishiAcAuto);
      break;
    case BRAND_SHARP:
      if      (ac.mode == "COOL") ac.sharp.setMode(kSharpAcCool);
      else if (ac.mode == "HEAT") ac.sharp.setMode(kSharpAcHeat);
      else if (ac.mode == "DRY")  ac.sharp.setMode(kSharpAcDry);
      else if (ac.mode == "FAN")  ac.sharp.setMode(kSharpAcFan);
      else                        ac.sharp.setMode(kSharpAcAuto);
      break;
    case BRAND_TOSHIBA:
      if      (ac.mode == "COOL") ac.toshiba.setMode(kToshibaAcCool);
      else if (ac.mode == "HEAT") ac.toshiba.setMode(kToshibaAcHeat);
      else if (ac.mode == "DRY")  ac.toshiba.setMode(kToshibaAcDry);
      else if (ac.mode == "FAN")  ac.toshiba.setMode(kToshibaAcFan);
      else                        ac.toshiba.setMode(kToshibaAcAuto);
      break;
  }
}

void sendIR(ACDevice& ac) {
  applyMode(ac);
  Serial.printf("[IR] room=%s id=%d brand=%s power=%s",
                currentRoom.c_str(), ac.id,
                brandName(ac.brand).c_str(), ac.power ? "ON" : "OFF");
  if (ac.power)
    Serial.printf(" temp=%d mode=%s fan=%s swing=%s",
                  ac.setTemp, ac.mode.c_str(),
                  ac.fanSpeed.c_str(), ac.swing.c_str());
  Serial.println();

  switch (ac.brand) {
    case BRAND_GREE:       ac.gree.setPower(ac.power);       ac.gree.setTemp(ac.setTemp);       ac.gree.send(); break;
    case BRAND_DAIKIN:     ac.daikin.setPower(ac.power);     ac.daikin.setTemp(ac.setTemp);     ac.daikin.send(); break;
    case BRAND_PANASONIC:  ac.panasonic.setPower(ac.power);  ac.panasonic.setTemp(ac.setTemp);  ac.panasonic.send(); break;
    case BRAND_LG:         ac.lg.setPower(ac.power);         ac.lg.setTemp(ac.setTemp);         ac.lg.send(); break;
    case BRAND_MITSUBISHI: ac.mitsubishi.setPower(ac.power); ac.mitsubishi.setTemp(ac.setTemp); ac.mitsubishi.send(); break;
    case BRAND_SHARP:      ac.sharp.setPower(ac.power);      ac.sharp.setTemp(ac.setTemp);      ac.sharp.send(); break;
    case BRAND_TOSHIBA:    ac.toshiba.setPower(ac.power);    ac.toshiba.setTemp(ac.setTemp);    ac.toshiba.send(); break;
  }
  delay(300);
}

// ----------------------------------------------------------
//  Safe publish — track failure & force reconnect jika 3x gagal
// ----------------------------------------------------------
bool safePublish(const char* topic, const char* payload, bool retain) {
  if (!client.connected()) {
    consecutivePublishFails++;
    return false;
  }

  bool ok = client.publish(topic, payload, retain);

  if (ok) {
    consecutivePublishFails = 0;
  } else {
    consecutivePublishFails++;
    Serial.printf("[MQTT] publish FAIL (%d/%d) topic=%s\n",
                  consecutivePublishFails, MAX_PUBLISH_FAILURES, topic);
  }

  // Deteksi half-open TCP: paksa reconnect kalau publish gagal terus
  if (consecutivePublishFails >= MAX_PUBLISH_FAILURES) {
    Serial.println("[MQTT] Too many publish failures, forcing reconnect...");
    client.disconnect();
    consecutivePublishFails = 0;
  }

  return ok;
}

// ----------------------------------------------------------
//  Publish helpers
// ----------------------------------------------------------
void publishStatus(ACDevice& ac) {
  StaticJsonDocument<256> doc;
  doc["room"]      = currentRoom;
  doc["ac_id"]     = ac.id;
  doc["brand"]     = brandName(ac.brand);
  doc["power"]     = ac.power ? "ON" : "OFF";
  doc["temp"]      = ac.setTemp;
  doc["ac_temp"]   = ac.setTemp;
  doc["mode"]      = ac.mode;
  doc["fan_speed"] = ac.fanSpeed;
  doc["swing"]     = ac.swing;

  char buf[256];
  serializeJson(doc, buf, sizeof(buf));

  char topic[80];
  sprintf(topic, "room/%s/ac/%d/status", currentRoom.c_str(), ac.id);
  safePublish(topic, buf, true);
}

void publishAllStatus() {
  if (acList.empty() || currentRoom == "") return;
  if (millis() - lastStatusPublish < STATUS_INTERVAL) return;
  lastStatusPublish = millis();

  for (auto& ac : acList) {
    publishStatus(ac);
  }
}

void publishDHT() {
  if (millis() - lastSensorPublish < SENSOR_INTERVAL) return;
  lastSensorPublish = millis();

  float temp = dht.readTemperature();
  float hum  = dht.readHumidity();

  if (isnan(temp) || isnan(hum)) {
    Serial.println("[DHT] baca gagal, skip");
    return;
  }

  StaticJsonDocument<256> doc;
  doc["device_id"] = DEVICE_ID;
  doc["room"]      = currentRoom;
  doc["suhu"]      = temp;
  doc["humidity"]  = hum;
  doc["ts_ms"]     = (uint32_t)millis();

  char buf[256];
  serializeJson(doc, buf, sizeof(buf));

  char topicDev[80];
  sprintf(topicDev, "device/%s/sensor", DEVICE_ID);
  bool ok1 = safePublish(topicDev, buf, true);
  Serial.printf("[DHT] %s %.1fC %s\n", topicDev, temp, ok1 ? "OK" : "FAIL");

  if (currentRoom != "") {
    String safeRoom = currentRoom;
    safeRoom.replace(" ", "_");
    char topicRoom[120];
    sprintf(topicRoom, "room/%s/sensor", safeRoom.c_str());
    safePublish(topicRoom, buf, true);
  }
}

void publishPing() {
  static unsigned long lastPing = 0;
  if (millis() - lastPing < PING_INTERVAL) return;
  lastPing = millis();

  StaticJsonDocument<128> doc;
  doc["device_id"] = DEVICE_ID;
  doc["ts_ms"]     = (uint32_t)millis();

  char buf[128];
  serializeJson(doc, buf, sizeof(buf));

  char topic[80];
  sprintf(topic, "device/%s/ping", DEVICE_ID);

  bool ok = safePublish(topic, buf, false);
  Serial.printf("[PING] %s %s\n", topic, ok ? "OK" : "FAIL");
}

void publishOnline() {
  char topic[60];
  sprintf(topic, "device/%s/online", DEVICE_ID);
  char payload[80];
  sprintf(payload, "{\"device_id\":\"%s\"}", DEVICE_ID);
  safePublish(topic, payload, false);
  Serial.printf("[MQTT] online -> %s\n", topic);
}

// ----------------------------------------------------------
//  MQTT Callback
// ----------------------------------------------------------
void callback(char* topic, uint8_t* payload, unsigned int length) {
  char msg[512];
  if (length >= sizeof(msg)) {
    Serial.println("[MQTT] payload terlalu besar");
    return;
  }
  memcpy(msg, payload, length);
  msg[length] = '\0';

  Serial.printf("[MQTT] << %s : %s\n", topic, msg);

  StaticJsonDocument<512> doc;
  if (deserializeJson(doc, msg)) {
    Serial.println("[MQTT] JSON parse error");
    return;
  }

  String t = String(topic);

  // === CONFIG ===
  String configTopic = "device/" + String(DEVICE_ID) + "/config";
  if (t == configTopic) {
    if (!doc.containsKey("room")) {
      Serial.println("[CONFIG] field 'room' tidak ada");
      return;
    }

    if (currentRoom != "") {
      char oldSub[80];
      sprintf(oldSub, "room/%s/ac/+/control", currentRoom.c_str());
      client.unsubscribe(oldSub);
    }

    acList.clear();
    currentRoom = doc["room"].as<String>();
    currentRoom.toLowerCase();
    Serial.printf("[CONFIG] room=%s\n", currentRoom.c_str());

    if (doc.containsKey("acs")) {
      for (JsonObject ac : doc["acs"].as<JsonArray>()) {
        int id = ac["id"];
        addAC(id, brandFromString(ac["brand"].as<String>()));
      }
    }

    char sub[80];
    sprintf(sub, "room/%s/ac/+/control", currentRoom.c_str());
    client.subscribe(sub);
    Serial.printf("[CONFIG] subscribed -> %s\n", sub);

    configReceived = true;
    return;
  }

  // === CLEAR ===
  String clearTopic = "device/" + String(DEVICE_ID) + "/clear";
  if (t == clearTopic) {
    if (currentRoom != "") {
      char oldSub[80];
      sprintf(oldSub, "room/%s/ac/+/control", currentRoom.c_str());
      client.unsubscribe(oldSub);
    }
    currentRoom = "";
    configReceived = false;
    acList.clear();
    Serial.println("[CLEAR] ESP tidak terdaftar");
    return;
  }

  // === AC CONTROL ===
  if (currentRoom == "") return;

  char roomParsed[50];
  int  acId = 0;
  if (sscanf(topic, "room/%[^/]/ac/%d/control", roomParsed, &acId) != 2) return;

  String topicRoom = String(roomParsed);
  topicRoom.toLowerCase();
  if (topicRoom != currentRoom) return;

  for (auto& ac : acList) {
    if (ac.id != acId) continue;

    static unsigned long lastIR = 0;
    if (millis() - lastIR < 500) continue;
    lastIR = millis();

    bool needSend = false;

    if (doc.containsKey("power")) {
      ac.power = (doc["power"].as<String>() == "ON");
      needSend = true;
    }
    if (doc.containsKey("mode")) {
      ac.mode = doc["mode"].as<String>();
      ac.mode.toUpperCase();
      if (ac.power) needSend = true;
    }
    if (doc.containsKey("temp")) {
      ac.setTemp = constrain(doc["temp"].as<int>(), 16, 30);
      if (ac.power) needSend = true;
    }
    if (doc.containsKey("fan_speed")) {
      ac.fanSpeed = doc["fan_speed"].as<String>();
      ac.fanSpeed.toUpperCase();
    }
    if (doc.containsKey("swing")) {
      ac.swing = doc["swing"].as<String>();
      ac.swing.toUpperCase();
    }

    if (needSend) {
      sendIR(ac);
      publishStatus(ac);
      client.loop();
    }
    break;
  }
}

// ----------------------------------------------------------
//  WiFi
// ----------------------------------------------------------
void connectWiFi() {
  if (WiFi.status() == WL_CONNECTED) return;

  Serial.printf("[WiFi] Connecting to %s\n", WIFI_SSID);
  WiFi.disconnect(true);
  delay(200);
  WiFi.mode(WIFI_STA);
  WiFi.setSleep(false);
  WiFi.setAutoReconnect(true);
  WiFi.persistent(false);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  int retry = 0;
  while (WiFi.status() != WL_CONNECTED && retry < 30) {
    delay(500);
    Serial.print(".");
    retry++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.printf("\n[WiFi] Connected IP=%s RSSI=%d\n",
                  WiFi.localIP().toString().c_str(), WiFi.RSSI());
  } else {
    Serial.println("\n[WiFi] FAIL");
  }
}

// ----------------------------------------------------------
//  MQTT Reconnect
// ----------------------------------------------------------
void reconnect() {
  while (!client.connected()) {
    if (WiFi.status() != WL_CONNECTED) {
      connectWiFi();
      if (WiFi.status() != WL_CONNECTED) {
        delay(2000);
        continue;
      }
    }

    uint64_t chipid = ESP.getEfuseMac();
    String cid = "ESP32_" + String(DEVICE_ID) + "_" +
                 String((uint32_t)(chipid >> 32), HEX);

    Serial.printf("[MQTT] Connecting %s:%d as %s\n",
                  MQTT_HOST, MQTT_PORT, cid.c_str());

    // LWT
    char lwtTopic[60];
    sprintf(lwtTopic, "device/%s/status", DEVICE_ID);

    if (client.connect(cid.c_str(), MQTT_USER, MQTT_PASS,
                       lwtTopic, 1, true, "offline")) {

      Serial.println("[MQTT] CONNECTED");
      consecutivePublishFails = 0;

      // status online (retain=true override LWT offline)
      char stTopic[60];
      sprintf(stTopic, "device/%s/status", DEVICE_ID);
      client.publish(stTopic, "online", true);

      publishOnline();
      client.loop();

      char subConfig[80]; sprintf(subConfig, "device/%s/config", DEVICE_ID);
      char subClear[80];  sprintf(subClear,  "device/%s/clear",  DEVICE_ID);
      client.subscribe(subConfig);
      client.subscribe(subClear);
      Serial.printf("[MQTT] subscribed: %s, %s\n", subConfig, subClear);

      if (currentRoom != "") {
        char subCtrl[80];
        sprintf(subCtrl, "room/%s/ac/+/control", currentRoom.c_str());
        client.subscribe(subCtrl);
        Serial.printf("[MQTT] re-subscribed: %s\n", subCtrl);
      }

      startWait = millis();

    } else {
      Serial.printf("[MQTT] FAIL state=%d, retry 5s\n", client.state());
      delay(5000);
    }
  }
}

// ----------------------------------------------------------
//  Setup
// ----------------------------------------------------------
void setup() {
  Serial.begin(115200);
  delay(500);
  dht.begin();

  Serial.println("================================");
  Serial.println(" ESP32 AC REMOTE CONTROLLER");
  Serial.println(" Device: " DEVICE_ID);
  Serial.println("================================");

  connectWiFi();

  espClient.setInsecure();          // TLS tanpa verifikasi cert
  client.setBufferSize(2048);
  client.setKeepAlive(20);          // PING ke broker tiap ~15s (lebih agresif)
  client.setSocketTimeout(8);       // deteksi connection dead lebih cepat
  client.setServer(MQTT_HOST, MQTT_PORT);
  client.setCallback(callback);
}

// ----------------------------------------------------------
//  Loop
// ----------------------------------------------------------
void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  if (!client.connected()) {
    reconnect();
  }

  client.loop();

  publishPing();
  publishDHT();

  if (!configReceived) {
    if (millis() - startWait > CONFIG_TIMEOUT) {
      Serial.println("[CONFIG] timeout, minta ulang");
      publishOnline();
      startWait = millis();
    }
    return;
  }

  publishAllStatus();
}
