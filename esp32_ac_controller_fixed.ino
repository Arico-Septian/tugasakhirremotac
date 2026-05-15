#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <DHT.h>
#include <IRremoteESP8266.h>
#include <IRsend.h>
#include <ir_Aircon.h>  // IRac generic class — semua brand
#include <vector>

// =================== WiFi ===================
const char* ssid     = "Morrow";
const char* password = "bawok123";

// =================== MQTT (HiveMQ Cloud TLS) ===================
const char* mqtt_server = "4edb07bbc98d48ba9e9154ee9bf84ccd.s1.eu.hivemq.cloud";
const int   mqtt_port   = 8883;
const char* mqtt_user   = "Arico";
const char* mqtt_pass   = "Arico170903";

// =================== Device ===================
#define DEVICE_ID "esp32_01"
String currentRoom = "";

// =================== Net Clients ===================
WiFiClientSecure espClient;
PubSubClient     client(espClient);

// =================== Timing ===================
bool          configReceived  = false;
unsigned long configTimeout   = 10000;
unsigned long startWait       = 0;

unsigned long lastPing        = 0;
const  long   pingInterval    = 15000;

unsigned long lastPublish     = 0;
const  long   publishInterval = 10000;

// =================== DHT ===================
#define DHTPIN  4
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

unsigned long lastSensorPublish = 0;
const  long   sensorPublishInterval = 5000;

// =================== AC dengan IRac generic ===================
int getPinById(int id) {
  switch (id) {
    case 1:  return 14;
    case 2:  return 27;
    case 3:  return 26;
    case 4:  return 25;
    case 5:  return 33;
    case 6:  return 32;
    case 7:  return 23;
    case 8:  return 22;
    case 9:  return 21;
    case 10: return 19;
    case 11: return 18;
    case 12: return 15;
    case 13: return 13;
    case 14: return 12;
    case 15: return 5;
    default: return -1;
  }
}

String protocolToStr(decode_type_t proto) {
  switch (proto) {
    case decode_type_t::GREE:       return "GREE";
    case decode_type_t::DAIKIN:     return "DAIKIN";
    case decode_type_t::PANASONIC:  return "PANASONIC";
    case decode_type_t::LG:         return "LG";
    case decode_type_t::MITSUBISHI_AC: return "MITSUBISHI";
    case decode_type_t::SHARP_AC:   return "SHARP";
    case decode_type_t::TOSHIBA_AC: return "TOSHIBA";
    default:                        return "UNKNOWN";
  }
}

decode_type_t strToProtocol(const String& s) {
  String b = s;
  b.toUpperCase();
  if (b == "DAIKIN")     return decode_type_t::DAIKIN;
  if (b == "PANASONIC")  return decode_type_t::PANASONIC;
  if (b == "LG")         return decode_type_t::LG;
  if (b == "MITSUBISHI") return decode_type_t::MITSUBISHI_AC;
  if (b == "SHARP")      return decode_type_t::SHARP_AC;
  if (b == "TOSHIBA")    return decode_type_t::TOSHIBA_AC;
  return decode_type_t::GREE;
}

struct ACDevice {
  int            id;
  decode_type_t  protocol;
  bool           power;
  int            setTemp;
  String         mode;       // AUTO/COOL/HEAT/DRY/FAN
  String         fanSpeed;   // AUTO/LOW/MEDIUM/HIGH
  String         swing;      // OFF/FULL/HALF/DOWN
  uint8_t        irPin;
  IRac           ac;         // single generic IR instance

  ACDevice(int _id, decode_type_t _proto, uint8_t _pin)
    : id(_id), protocol(_proto),
      power(false), setTemp(24),
      mode("AUTO"), fanSpeed("AUTO"), swing("OFF"),
      irPin(_pin),
      ac(_pin) {
    ac.setProtocol(protocol);
  }
};

std::vector<ACDevice> acList;

String normalizeRoom(const String& in) {
  String r = in;
  r.trim();
  r.toLowerCase();
  r.replace(" ", "_");
  return r;
}

void addAC(int id, decode_type_t proto) {
  for (auto& ac : acList)
    if (ac.id == id) return;

  int pin = getPinById(id);
  if (pin == -1) {
    Serial.println("❌ ID AC tidak memiliki mapping PIN!");
    return;
  }

  acList.push_back(ACDevice(id, proto, pin));
  Serial.printf("✅ AC ditambahkan | ROOM: %s | ID: %d | BRAND: %s | IR PIN: %d\n",
                currentRoom.c_str(), id, protocolToStr(proto).c_str(), pin);
}

void sendIR(ACDevice& ac) {
  // Build universal command struct
  stdAc::ac_command_t cmd = {};
  cmd.protocol = ac.protocol;
  cmd.power    = ac.power ? stdAc::opmode_t::kPowerOn : stdAc::opmode_t::kPowerOff;
  cmd.celsius  = ac.setTemp;

  // Mode
  if      (ac.mode == "COOL") cmd.mode = stdAc::opmode_t::kCool;
  else if (ac.mode == "HEAT") cmd.mode = stdAc::opmode_t::kHeat;
  else if (ac.mode == "DRY")  cmd.mode = stdAc::opmode_t::kDry;
  else if (ac.mode == "FAN")  cmd.mode = stdAc::opmode_t::kFan;
  else                        cmd.mode = stdAc::opmode_t::kAuto;

  // Fan speed
  if      (ac.fanSpeed == "LOW")    cmd.fanspeed = stdAc::fanspeed_t::kMin;
  else if (ac.fanSpeed == "MEDIUM") cmd.fanspeed = stdAc::fanspeed_t::kMed;
  else if (ac.fanSpeed == "HIGH")   cmd.fanspeed = stdAc::fanspeed_t::kMax;
  else                              cmd.fanspeed = stdAc::fanspeed_t::kAuto;

  // Swing
  if      (ac.swing == "FULL") cmd.swingv = stdAc::swingv_t::kAuto;
  else if (ac.swing == "HALF") cmd.swingv = stdAc::swingv_t::kMiddle;
  else if (ac.swing == "DOWN") cmd.swingv = stdAc::swingv_t::kLowest;
  else                         cmd.swingv = stdAc::swingv_t::kOff;

  Serial.println("========== IR COMMAND ==========");
  Serial.printf("ROOM: %s | AC ID: %d | BRAND: %s | POWER: %s\n",
                currentRoom.c_str(), ac.id, protocolToStr(ac.protocol).c_str(),
                ac.power ? "ON" : "OFF");
  if (ac.power) {
    Serial.printf("TEMP: %d | MODE: %s | FAN: %s | SWING: %s\n",
                  ac.setTemp, ac.mode.c_str(), ac.fanSpeed.c_str(), ac.swing.c_str());
  }
  Serial.println("================================");

  // Single send call untuk semua brand
  ac.ac.sendAc(cmd);
  delay(300);
}

// =================== MQTT Publish helpers ===================
void publishAcStatus(ACDevice& ac) {
  if (currentRoom == "") return;

  StaticJsonDocument<256> doc;
  doc["room"]      = currentRoom;
  doc["ac_id"]     = ac.id;
  doc["brand"]     = protocolToStr(ac.protocol);
  doc["power"]     = ac.power ? "ON" : "OFF";
  doc["temp"]      = ac.setTemp;
  doc["ac_temp"]   = ac.setTemp;
  doc["mode"]      = ac.mode;
  doc["fan_speed"] = ac.fanSpeed;
  doc["swing"]     = ac.swing;

  char buf[256];
  size_t n = serializeJson(doc, buf, sizeof(buf));

  char topic[96];
  snprintf(topic, sizeof(topic), "room/%s/ac/%d/status",
           currentRoom.c_str(), ac.id);

  bool ok = client.publish(topic, (const uint8_t*)buf, n, true);
  Serial.printf("STATUS → %s %s\n", topic, ok ? "✅" : "❌");
}

void publishAllAc() {
  if (acList.empty() || currentRoom == "") return;
  if (millis() - lastPublish < publishInterval) return;
  lastPublish = millis();

  for (auto& ac : acList) publishAcStatus(ac);
}

void publishDHT() {
  if (millis() - lastSensorPublish < sensorPublishInterval) return;
  lastSensorPublish = millis();

  float suhu = dht.readTemperature();
  float hum  = dht.readHumidity();
  if (isnan(suhu) || isnan(hum)) {
    Serial.println("❌ DHT NAN (skip publish)");
    return;
  }

  StaticJsonDocument<256> doc;
  doc["device_id"] = DEVICE_ID;
  doc["room"]      = currentRoom;
  doc["suhu"]      = suhu;
  doc["humidity"]  = hum;
  doc["ts_ms"]     = (uint32_t)millis();

  char buf[256];
  size_t n = serializeJson(doc, buf, sizeof(buf));

  char topicDev[80];
  snprintf(topicDev, sizeof(topicDev), "device/%s/sensor", DEVICE_ID);
  client.publish(topicDev, (const uint8_t*)buf, n, false);

  if (currentRoom != "") {
    char topicRoom[120];
    snprintf(topicRoom, sizeof(topicRoom), "room/%s/sensor", currentRoom.c_str());
    client.publish(topicRoom, (const uint8_t*)buf, n, false);
    Serial.printf("🌡️ DHT %.1fC %.0f%% → %s\n", suhu, hum, topicRoom);
  }
}

// =================== MQTT Callback ===================
void callback(char* topic, uint8_t* payload, unsigned int length) {
  String msg;
  msg.reserve(length + 1);
  for (unsigned int i = 0; i < length; i++) msg += (char)payload[i];

  Serial.println("=================================");
  Serial.printf("MQTT IN  %s\n", topic);
  Serial.printf("PAYLOAD  %s\n", msg.c_str());
  Serial.println("=================================");

  String t = String(topic);

  // ---------- CONFIG ----------
  String configTopic = "device/" + String(DEVICE_ID) + "/config";
  if (t == configTopic) {
    StaticJsonDocument<2048> doc;
    if (deserializeJson(doc, msg)) {
      Serial.println("❌ JSON CONFIG INVALID");
      return;
    }

    configReceived = true;

    if (!doc.containsKey("room")) {
      Serial.println("⚠️ CONFIG tanpa field 'room'");
      return;
    }

    if (currentRoom != "") {
      char oldSub[96];
      snprintf(oldSub, sizeof(oldSub), "room/%s/ac/+/control", currentRoom.c_str());
      client.unsubscribe(oldSub);
      Serial.printf("🔁 Unsubscribed: %s\n", oldSub);
    }

    acList.clear();
    currentRoom = normalizeRoom(doc["room"].as<String>());
    Serial.printf("📌 ROOM = %s\n", currentRoom.c_str());

    if (doc.containsKey("acs")) {
      for (JsonObject ac : doc["acs"].as<JsonArray>()) {
        int id = ac["id"] | 0;
        if (id <= 0) continue;
        String br = ac["brand"] | "GREE";
        addAC(id, strToProtocol(br));
      }
    }

    char sub1[96];
    snprintf(sub1, sizeof(sub1), "room/%s/ac/+/control", currentRoom.c_str());
    client.subscribe(sub1, 1);
    Serial.printf("📥 SUBSCRIBE %s (QoS1)\n", sub1);
    return;
  }

  // ---------- CLEAR ----------
  String clearTopic = "device/" + String(DEVICE_ID) + "/clear";
  if (t == clearTopic) {
    Serial.println("🧹 ROOM dihapus oleh server");
    if (currentRoom != "") {
      char oldSub[96];
      snprintf(oldSub, sizeof(oldSub), "room/%s/ac/+/control", currentRoom.c_str());
      client.unsubscribe(oldSub);
    }
    currentRoom = "";
    acList.clear();
    return;
  }

  // ---------- CONTROL ----------
  if (currentRoom == "") return;

  char roomRaw[64];
  int  ac_id = 0;
  if (sscanf(topic, "room/%63[^/]/ac/%d/control", roomRaw, &ac_id) != 2) return;

  String topicRoom = normalizeRoom(String(roomRaw));
  if (topicRoom != currentRoom) return;

  StaticJsonDocument<256> doc;
  if (deserializeJson(doc, msg)) {
    Serial.println("❌ JSON CONTROL INVALID");
    return;
  }

  for (auto& ac : acList) {
    if (ac.id != ac_id) continue;

    static unsigned long lastIR = 0;
    if (millis() - lastIR < 500) return;
    lastIR = millis();

    bool needSend = false;

    if (doc.containsKey("power")) {
      String p = doc["power"].as<String>();
      p.toUpperCase();
      ac.power = (p == "ON");
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
      if (ac.power) needSend = true;
    }
    if (doc.containsKey("swing")) {
      ac.swing = doc["swing"].as<String>();
      ac.swing.toUpperCase();
      if (ac.power) needSend = true;
    }

    if (needSend) {
      sendIR(ac);
      publishAcStatus(ac);
    }
    return;
  }
}

// =================== WiFi / MQTT lifecycle ===================
void connectWiFi() {
  Serial.printf("Connecting to WiFi: %s\n", ssid);

  WiFi.mode(WIFI_STA);
  WiFi.setSleep(false);
  WiFi.setAutoReconnect(true);
  WiFi.persistent(false);
  WiFi.begin(ssid, password);

  int retry = 0;
  while (WiFi.status() != WL_CONNECTED && retry < 40) {
    delay(500);
    Serial.print(".");
    retry++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.printf("\n✅ WiFi OK | IP: %s\n", WiFi.localIP().toString().c_str());
  } else {
    Serial.println("\n❌ WiFi GAGAL");
  }
}

void mqttReconnect() {
  while (!client.connected()) {
    uint64_t chipid = ESP.getEfuseMac();
    String cid = "ESP32_" + String(DEVICE_ID) + "_" + String((uint32_t)(chipid >> 32), HEX);

    Serial.printf("MQTT connect → %s:%d as %s\n", mqtt_server, mqtt_port, cid.c_str());

    char lwtTopic[80];
    snprintf(lwtTopic, sizeof(lwtTopic), "device/%s/status", DEVICE_ID);

    bool ok = client.connect(
      cid.c_str(),
      mqtt_user, mqtt_pass,
      lwtTopic, 1, true, "offline", true
    );

    if (ok) {
      Serial.println("✅ MQTT CONNECTED");

      client.publish(lwtTopic, "online", false);

      char onlineTopic[80];
      snprintf(onlineTopic, sizeof(onlineTopic), "device/%s/online", DEVICE_ID);
      char onlinePayload[80];
      snprintf(onlinePayload, sizeof(onlinePayload), "{\"device_id\":\"%s\"}", DEVICE_ID);
      client.publish(onlineTopic, onlinePayload, false);

      char subConfig[80], subClear[80];
      snprintf(subConfig, sizeof(subConfig), "device/%s/config", DEVICE_ID);
      snprintf(subClear,  sizeof(subClear),  "device/%s/clear",  DEVICE_ID);
      client.subscribe(subConfig, 1);
      client.subscribe(subClear, 1);
      Serial.printf("📥 SUBSCRIBE %s\n📥 SUBSCRIBE %s\n", subConfig, subClear);

      configReceived = false;
      startWait      = millis();
    } else {
      Serial.printf("❌ MQTT fail, state=%d → retry 5s\n", client.state());
      delay(5000);
    }
  }
}

// =================== Setup / Loop ===================
void setup() {
  Serial.begin(115200);
  delay(500);
  dht.begin();

  Serial.println("================================");
  Serial.println("ESP32 AC REMOTE — IRac generic");
  Serial.printf("DEVICE_ID = %s\n", DEVICE_ID);
  Serial.println("================================");

  connectWiFi();

  espClient.setInsecure();
  client.setServer(mqtt_server, mqtt_port);
  client.setBufferSize(2048);
  client.setKeepAlive(60);
  client.setSocketTimeout(15);
  client.setCallback(callback);
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) connectWiFi();
  if (!client.connected())           mqttReconnect();
  client.loop();

  if (!configReceived) {
    if (millis() - startWait > configTimeout) {
      Serial.println("⚠️ Config tidak diterima, paksa reconnect...");
      client.disconnect();
      startWait = millis();
    }
    return;
  }

  if (millis() - lastPing > pingInterval) {
    char topic[64];
    snprintf(topic, sizeof(topic), "device/%s/ping", DEVICE_ID);
    client.publish(topic, "1", false);
    Serial.printf("💓 PING → %s\n", topic);
    lastPing = millis();
  }

  publishAllAc();
  publishDHT();
}
