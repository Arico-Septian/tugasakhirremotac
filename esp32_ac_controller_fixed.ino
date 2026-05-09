#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
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

// WiFi Credentials
const char* ssid = "TUGASAKHIR";
const char* password = "kopisusu";

// MQTT Credentials - HIVEMQ CLOUD
const char* mqtt_server = "4edb07bbc98d48ba9e9154ee9bf84ccd.s1.eu.hivemq.cloud";
const int mqtt_port = 8883; // TLS port
const char* mqtt_user = "Arico";
const char* mqtt_pass = "Arico170903";

// Device Config
#define DEVICE_ID "esp32_01"
String currentRoom = "";

// MQTT & WiFi
WiFiClientSecure espClient;
PubSubClient client(espClient);

// Timing
bool configReceived = false;
unsigned long configTimeout = 10000;
unsigned long startWait = millis();
unsigned long lastPing = 0;
const long pingInterval = 30000; // 30 detik
unsigned long lastPublish = 0;
const long publishInterval = 10000; // 10 detik

// AC Brand Enum
enum ACBrand {
  BRAND_GREE,
  BRAND_DAIKIN,
  BRAND_PANASONIC,
  BRAND_LG,
  BRAND_MITSUBISHI,
  BRAND_SHARP,
  BRAND_TOSHIBA
};

struct ACDevice;
void sendIR(ACDevice& ac);
String brandToStrX(ACBrand brand);

int getPinById(int id) {
  switch (id) {
    case 1: return 14;
    case 2: return 27;
    case 3: return 26;
    case 4: return 25;
    case 5: return 33;
    case 6: return 32;
    case 7: return 23;
    case 8: return 22;
    case 9: return 21;
    case 10: return 19;
    case 11: return 18;
    case 12: return 15;
    case 13: return 13;
    case 14: return 12;
    case 15: return 5;
    default: return -1;
  }
}

String brandToStrX(ACBrand brand) {
  switch (brand) {
    case BRAND_GREE: return "GREE";
    case BRAND_DAIKIN: return "DAIKIN";
    case BRAND_PANASONIC: return "PANASONIC";
    case BRAND_LG: return "LG";
    case BRAND_MITSUBISHI: return "MITSUBISHI";
    case BRAND_SHARP: return "SHARP";
    case BRAND_TOSHIBA: return "TOSHIBA";
    default: return "UNKNOWN";
  }
}

struct ACDevice {
  int id;
  ACBrand brand;
  bool power;
  int setTemp;
  String mode;
  String fanSpeed;
  String swing;
  uint8_t irPin;

  IRGreeAC gree;
  IRDaikinESP daikin;
  IRPanasonicAc panasonic;
  IRLgAc lg;
  IRMitsubishiAC mitsubishi;
  IRSharpAc sharp;
  IRToshibaAC toshiba;

  ACDevice(int _id, ACBrand _brand, uint8_t _pin)
    : id(_id), brand(_brand),
      power(false), setTemp(24), mode("AUTO"), fanSpeed("AUTO"), swing("OFF"), irPin(_pin),
      gree(_pin),
      daikin(_pin),
      panasonic(_pin),
      lg(_pin),
      mitsubishi(_pin),
      sharp(_pin),
      toshiba(_pin) {
    gree.begin();
    daikin.begin();
    panasonic.begin();
    lg.begin();
    mitsubishi.begin();
    sharp.begin();
    toshiba.begin();
  }
};

std::vector<ACDevice> acList;

void addAC(int id, ACBrand brand = BRAND_GREE) {
  for (auto& ac : acList)
    if (ac.id == id) return;

  int pin = getPinById(id);
  if (pin == -1) {
    Serial.println("❌ ID AC tidak memiliki mapping PIN!");
    return;
  }

  acList.push_back(ACDevice(id, brand, pin));

  Serial.print("✅ AC ditambahkan | ROOM: ");
  Serial.print(currentRoom);
  Serial.print(" | ID: ");
  Serial.print(id);
  Serial.print(" | BRAND: ");
  Serial.print(brandToStrX(brand));
  Serial.print(" | IR PIN: ");
  Serial.println(pin);
}

void removeAC(int id) {
  for (int i = 0; i < acList.size(); i++) {
    if (acList[i].id == id) {
      acList.erase(acList.begin() + i);
      Serial.println("AC dihapus ID: " + String(id));
      return;
    }
  }
}

void applyMode(ACDevice& ac) {
  if (ac.brand == BRAND_GREE) {
    if (ac.mode == "COOL") ac.gree.setMode(kGreeCool);
    else if (ac.mode == "HEAT") ac.gree.setMode(kGreeHeat);
    else if (ac.mode == "DRY") ac.gree.setMode(kGreeDry);
    else if (ac.mode == "FAN") ac.gree.setMode(kGreeFan);
    else ac.gree.setMode(kGreeAuto);
  }
  else if (ac.brand == BRAND_DAIKIN) {
    if (ac.mode == "COOL") ac.daikin.setMode(kDaikinCool);
    else if (ac.mode == "HEAT") ac.daikin.setMode(kDaikinHeat);
    else if (ac.mode == "DRY") ac.daikin.setMode(kDaikinDry);
    else if (ac.mode == "FAN") ac.daikin.setMode(kDaikinFan);
    else ac.daikin.setMode(kDaikinAuto);
  }
  else if (ac.brand == BRAND_PANASONIC) {
    if (ac.mode == "COOL") ac.panasonic.setMode(kPanasonicAcCool);
    else if (ac.mode == "HEAT") ac.panasonic.setMode(kPanasonicAcHeat);
    else if (ac.mode == "DRY") ac.panasonic.setMode(kPanasonicAcDry);
    else if (ac.mode == "FAN") ac.panasonic.setMode(kPanasonicAcFan);
    else ac.panasonic.setMode(kPanasonicAcAuto);
  }
  else if (ac.brand == BRAND_LG) {
    if (ac.mode == "COOL") ac.lg.setMode(kLgAcCool);
    else if (ac.mode == "HEAT") ac.lg.setMode(kLgAcHeat);
    else if (ac.mode == "DRY") ac.lg.setMode(kLgAcDry);
    else if (ac.mode == "FAN") ac.lg.setMode(kLgAcFan);
    else ac.lg.setMode(kLgAcAuto);
  }
  else if (ac.brand == BRAND_MITSUBISHI) {
    if (ac.mode == "COOL") ac.mitsubishi.setMode(kMitsubishiAcCool);
    else if (ac.mode == "HEAT") ac.mitsubishi.setMode(kMitsubishiAcHeat);
    else if (ac.mode == "DRY") ac.mitsubishi.setMode(kMitsubishiAcDry);
    else if (ac.mode == "FAN") ac.mitsubishi.setMode(kMitsubishiAcFan);
    else ac.mitsubishi.setMode(kMitsubishiAcAuto);
  }
  else if (ac.brand == BRAND_SHARP) {
    if (ac.mode == "COOL") ac.sharp.setMode(kSharpAcCool);
    else if (ac.mode == "HEAT") ac.sharp.setMode(kSharpAcHeat);
    else if (ac.mode == "DRY") ac.sharp.setMode(kSharpAcDry);
    else if (ac.mode == "FAN") ac.sharp.setMode(kSharpAcFan);
    else ac.sharp.setMode(kSharpAcAuto);
  }
  else if (ac.brand == BRAND_TOSHIBA) {
    if (ac.mode == "COOL") ac.toshiba.setMode(kToshibaAcCool);
    else if (ac.mode == "HEAT") ac.toshiba.setMode(kToshibaAcHeat);
    else if (ac.mode == "DRY") ac.toshiba.setMode(kToshibaAcDry);
    else if (ac.mode == "FAN") ac.toshiba.setMode(kToshibaAcFan);
    else ac.toshiba.setMode(kToshibaAcAuto);
  }
}

void sendIR(ACDevice& ac) {
  applyMode(ac);

  Serial.println("========== IR COMMAND ==========");
  Serial.print("ROOM: ");
  Serial.println(currentRoom);
  Serial.print("AC ID: ");
  Serial.println(ac.id);
  Serial.print("BRAND: ");
  Serial.println(brandToStrX(ac.brand));
  Serial.print("POWER: ");
  Serial.println(ac.power ? "ON" : "OFF");

  if (ac.power) {
    Serial.print("TEMP: ");
    Serial.println(ac.setTemp);
    Serial.print("MODE: ");
    Serial.println(ac.mode);
    Serial.print("FAN SPEED: ");
    Serial.println(ac.fanSpeed);
    Serial.print("SWING: ");
    Serial.println(ac.swing);
  }
  Serial.println("================================");

  switch (ac.brand) {
    case BRAND_GREE:
      ac.gree.setPower(ac.power);
      ac.gree.setTemp(ac.setTemp);
      ac.gree.send();
      break;

    case BRAND_DAIKIN:
      ac.daikin.setPower(ac.power);
      ac.daikin.setTemp(ac.setTemp);
      ac.daikin.send();
      break;

    case BRAND_PANASONIC:
      ac.panasonic.setPower(ac.power);
      ac.panasonic.setTemp(ac.setTemp);
      ac.panasonic.send();
      break;

    case BRAND_LG:
      ac.lg.setPower(ac.power);
      ac.lg.setTemp(ac.setTemp);
      ac.lg.send();
      break;

    case BRAND_MITSUBISHI:
      ac.mitsubishi.setPower(ac.power);
      ac.mitsubishi.setTemp(ac.setTemp);
      ac.mitsubishi.send();
      break;

    case BRAND_SHARP:
      ac.sharp.setPower(ac.power);
      ac.sharp.setTemp(ac.setTemp);
      ac.sharp.send();
      break;

    case BRAND_TOSHIBA:
      ac.toshiba.setPower(ac.power);
      ac.toshiba.setTemp(ac.setTemp);
      ac.toshiba.send();
      break;
  }
  delay(300);
}

void publishData() {
  if (acList.empty()) return;
  if (currentRoom == "") return;
  if (millis() - lastPublish < publishInterval) return;
  lastPublish = millis();

  for (auto& ac : acList) {
    StaticJsonDocument<256> doc;
    doc["room"] = currentRoom;
    doc["ac_id"] = ac.id;
    doc["brand"] = brandToStrX(ac.brand);
    doc["power"] = ac.power ? "ON" : "OFF";
    doc["temp"] = ac.setTemp;
    doc["ac_temp"] = ac.setTemp;
    doc["mode"] = ac.mode;
    doc["fan_speed"] = ac.fanSpeed;
    doc["swing"] = ac.swing;

    char buffer[256];
    serializeJson(doc, buffer, sizeof(buffer));

    char topic[80];
    sprintf(topic, "room/%s/ac/%d/status", currentRoom.c_str(), ac.id);
    client.publish(topic, buffer, true);
    Serial.print("Publish status → ");
    Serial.println(topic);
  }
}

void callback(char* topic, uint8_t* payload, unsigned int length) {
  char msg[256];
  if (length >= sizeof(msg)) return;
  memcpy(msg, payload, length);
  msg[length] = '\0';

  Serial.println("=================================");
  Serial.println("MQTT MESSAGE RECEIVED");
  Serial.print("Topic: ");
  Serial.println(topic);
  Serial.print("Payload: ");
  Serial.println(msg);
  Serial.println("=================================");

  StaticJsonDocument<256> doc;
  if (deserializeJson(doc, msg)) {
    Serial.println("❌ JSON PARSE ERROR");
    return;
  }

  String t = String(topic);

  String configTopic = "device/" + String(DEVICE_ID) + "/config";
  if (t == configTopic) {
    configReceived = true;

    if (!doc.containsKey("room")) {
      Serial.println("⚠️ CONFIG tidak valid: field 'room' tidak ada");
      return;
    }

    if (currentRoom != "") {
      char old1[80];
      sprintf(old1, "room/%s/ac/+/control", currentRoom.c_str());
      client.unsubscribe(old1);
      Serial.println("🔁 Unsubscribed dari room lama");
    }

    acList.clear();

    currentRoom = doc["room"].as<String>();
    currentRoom.toLowerCase();
    Serial.print("📌 ROOM diset dari server: ");
    Serial.println(currentRoom);

    if (doc.containsKey("acs")) {
      JsonArray arr = doc["acs"];

      for (JsonObject ac : arr) {
        int id = ac["id"];
        String brand = ac["brand"];

        if (brand == "DAIKIN") addAC(id, BRAND_DAIKIN);
        else if (brand == "PANASONIC") addAC(id, BRAND_PANASONIC);
        else if (brand == "LG") addAC(id, BRAND_LG);
        else if (brand == "MITSUBISHI") addAC(id, BRAND_MITSUBISHI);
        else if (brand == "SHARP") addAC(id, BRAND_SHARP);
        else if (brand == "TOSHIBA") addAC(id, BRAND_TOSHIBA);
        else addAC(id, BRAND_GREE);
      }

      Serial.println("✅ AC LIST DARI SERVER DIMUAT");
    }

    char sub1[80];
    sprintf(sub1, "room/%s/ac/+/control", currentRoom.c_str());
    client.subscribe(sub1);

    Serial.println("SUBSCRIBED:");
    Serial.println(sub1);
    return;
  }

  String clearTopic = "device/" + String(DEVICE_ID) + "/clear";
  if (t == clearTopic) {
    Serial.println("🧹 ROOM dihapus oleh server");

    if (currentRoom != "") {
      char sub1[80];
      sprintf(sub1, "room/%s/ac/+/control", currentRoom.c_str());
      client.unsubscribe(sub1);
      Serial.println("🔕 Unsubscribed dari room lama");
    }

    currentRoom = "";
    acList.clear();
    Serial.println("❌ Semua AC dihapus");
    Serial.println("📭 ESP sekarang tidak terdaftar di room manapun");
    return;
  }

  if (currentRoom == "") return;

  char room[50];
  int ac_id = 0;
  if (sscanf(topic, "room/%[^/]/ac/%d/control", room, &ac_id) != 2) return;

  String topicRoom = String(room);
  topicRoom.toLowerCase();

  if (topicRoom != currentRoom) return;

  for (auto& ac : acList) {
    if (ac.id != ac_id) continue;

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

      StaticJsonDocument<256> docSend;
      docSend["room"] = currentRoom;
      docSend["ac_id"] = ac.id;
      docSend["brand"] = brandToStrX(ac.brand);
      docSend["power"] = ac.power ? "ON" : "OFF";
      docSend["temp"] = ac.setTemp;
      docSend["ac_temp"] = ac.setTemp;
      docSend["mode"] = ac.mode;
      docSend["fan_speed"] = ac.fanSpeed;
      docSend["swing"] = ac.swing;

      char buffer[256];
      serializeJson(docSend, buffer);

      char topicPub[80];
      sprintf(topicPub, "room/%s/ac/%d/status", currentRoom.c_str(), ac.id);

      if (client.publish(topicPub, buffer, true)) {
        Serial.println("✅ Publish BERHASIL");
      } else {
        Serial.println("❌ Publish GAGAL");
      }

      client.loop();
      Serial.print("🚀 Publish langsung → ");
      Serial.println(topicPub);
    }
  }
}

void connectWiFi() {
  Serial.print("Connecting to WiFi: ");
  Serial.println(ssid);

  Serial.println("Scanning available networks...");
  int n = WiFi.scanNetworks();
  bool ssidFound = false;

  for (int i = 0; i < n; i++) {
    Serial.print("  - ");
    Serial.print(WiFi.SSID(i));
    Serial.print(" (Signal: ");
    Serial.print(WiFi.RSSI(i));
    Serial.println(" dBm)");

    if (WiFi.SSID(i) == ssid) {
      ssidFound = true;
      Serial.println("  ✅ SSID ditemukan!");
    }
  }

  if (!ssidFound) {
    Serial.println("❌ SSID tidak ditemukan!");
    Serial.println("Ganti SSID di kode atau gunakan WiFi lain.");
    return;
  }

  WiFi.mode(WIFI_STA);
  WiFi.disconnect(true);
  delay(200);

  WiFi.begin(ssid, password);

  int retry = 0;
  while (WiFi.status() != WL_CONNECTED && retry < 30) {
    delay(1000);
    Serial.print(".");
    if (retry % 5 == 0) {
      Serial.print(" Status: ");
      Serial.print(WiFi.status());
    }
    yield();
    retry++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi Connected!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\n❌ GAGAL CONNECT WIFI");
    Serial.print("Status: ");
    Serial.println(WiFi.status());
    Serial.println("Tips: ");
    Serial.println("  - Periksa password WiFi");
    Serial.println("  - Dekatkan ESP32 ke router");
    Serial.println("  - Pastikan WiFi menggunakan 2.4GHz (bukan 5GHz)");
  }
}

void reconnect() {
  while (!client.connected()) {
    uint64_t chipid = ESP.getEfuseMac();
    String cid = "ESP32_" + String(DEVICE_ID) + "_" + String((uint32_t)(chipid >> 32), HEX);

    espClient.setInsecure();

    Serial.print("Attempting MQTT connection to ");
    Serial.print(mqtt_server);
    Serial.print(":");
    Serial.println(mqtt_port);

    if (client.connect(cid.c_str(), mqtt_user, mqtt_pass)) {
      Serial.println("=================================");
      Serial.println("✅ MQTT CONNECTED");

      char topic[50];
      sprintf(topic, "device/%s/online", DEVICE_ID);
      char payload[80];
      sprintf(payload, "{\"device_id\":\"%s\"}", DEVICE_ID);

      client.publish(topic, payload, true);
      delay(200);
      client.loop();

      Serial.println("📡 ONLINE DIKIRIM");
      Serial.print("DEVICE: ");
      Serial.println(DEVICE_ID);
      Serial.print("Broker: ");
      Serial.println(mqtt_server);
      Serial.print("Port: ");
      Serial.println(mqtt_port);
      Serial.print("Client ID: ");
      Serial.println(cid);
      Serial.println("=================================");

      // Subscribe to config & clear topics
      char subConfig[80];
      sprintf(subConfig, "device/%s/config", DEVICE_ID);
      client.subscribe(subConfig);

      char subClear[80];
      sprintf(subClear, "device/%s/clear", DEVICE_ID);
      client.subscribe(subClear);

      delay(200);
      client.loop();

      Serial.println("SUBSCRIBED:");
      Serial.println(subConfig);
      Serial.println(subClear);
    } else {
      Serial.print("Failed, state=");
      Serial.println(client.state());
      Serial.println("Retrying in 5 seconds...");
      delay(5000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  delay(1000);

  Serial.println("================================");
  Serial.println("ESP32 AC REMOTE CONTROLLER");
  Serial.println("Booting device...");
  Serial.println("================================");

  Serial.println("SCAN WIFI...");
  int n = WiFi.scanNetworks();

  if (n == 0) {
    Serial.println("❌ Tidak ada WiFi ditemukan");
  } else {
    for (int i = 0; i < n; i++) {
      Serial.print("SSID: ");
      Serial.println(WiFi.SSID(i));
    }
  }

  connectWiFi();

  espClient.setInsecure();

  client.setKeepAlive(10);
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  if (!client.connected()) {
    reconnect();
  }

  client.loop();

  if (!configReceived) {
    if (millis() - startWait > configTimeout) {
      Serial.println("⚠️ Config tidak diterima, retry...");
      reconnect();
      startWait = millis();
    }
    return;
  }

  if (millis() - lastPing > pingInterval) {
    char topic[50];
    sprintf(topic, "device/%s/ping", DEVICE_ID);
    client.publish(topic, "1");

    Serial.print("💓 PING → ");
    Serial.println(topic);

    lastPing = millis();
  }

  publishData();
}
