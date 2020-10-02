/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */


//Include 3rd party libraries - Make sure to downlaod them in advance with the Arduino Library Manager
#include <Crypto.h> //Needed to calcualte SHA265HMAC Hash
#include <ESP8266WiFi.h> //Needed to make HTTP Requests
#include <ArduinoJson.h> //Needed to convert data into JSON format
#include <NTPClient.h> //Needed to get current time
#include <WiFiUdp.h> //Needed to get current time
ADC_MODE(ADC_VCC);

//WiFi Configuration - Replace parameters with your own WiFi.
const char* ssid = "XXXXXX"; //WiFi SSID.
const char* password = "XXXXXX"; //WiFi Password.

//API configuration.
const char* server = "192.168.0.29"; //IP of your server where API is hosted.
const char* channelname = "channel12"; //Channel to query - create first in your admin panel.
String apiKey = "fb1471ae7423b141"; //API Write Key - get it from your admin panel

int count;
long currenttime;

WiFiClient client;

//make sure your WiFi is connected to the internet to query the current timestamp.
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "europe.pool.ntp.org", 0, 6000); //timestamp server

String formattedDate;
String dayStamp;
String timeStamp;

String Hash_SHA256;

String JSON_Header;
String JSON_Payload;


void setup() {
	//Establish serial connection to priont current status e.g. for debugging.
	Serial.begin(9600);
	delay(10);

	//Connect to given WiFI.
	connectWifi();
	//Connect to time server.
	timeClient.begin();
}

// the loop routine runs over and over again forever:
void loop() {

	/*
	double rawdata = getAnalogReadLP();
	double voltage = rawdata * ((VCC / 2) / 1024);
	double current = voltage * VCC_TO_AMP;
	double power = voltage * VCC_TO_AMP * AMP_TO_POW;
	*/
	currenttime = getTime();
	sendPower();
	//getToken();


	delay(5000);

}

void connectWifi(){
	WiFi.begin(ssid, password);

	Serial.println();
	Serial.println();
	Serial.print("Connecting to ");
	Serial.println(ssid);

	WiFi.begin(ssid, password);

	while (WiFi.status() != WL_CONNECTED) {
	delay(100);
	Serial.print(".");
	}
	Serial.println("");
	Serial.println("WiFi connected");
}


void makeJSON(){
    
    StaticJsonBuffer<300> jsonBuffer;

    JsonArray& payload = jsonBuffer.createArray();
    JsonObject& payload_0 = payload.createNestedObject();

    payload_0["var0"] = ESP.getVcc();
    payload_0["var1"] = random(20, 30);
    payload_0["var2"] = random(20, 30);
    payload_0["var3"] = random(20, 30);
    payload_0["var4"] = random(20, 30);
    
    JSON_Payload = "";
    payload.printTo(JSON_Payload);
    jsonBuffer.clear();

    StaticJsonBuffer<300> JBuffer;

    //create header to make sure server can verify signature
    JsonObject& JWTHeader = JBuffer.createObject();
    JWTHeader["channelname"] = channelname;
    JWTHeader["subchannel"] = "all";
    JWTHeader["count"] = payload.size();
    JWTHeader["timestamp"] = currenttime;

    JSON_Header = "";
    JWTHeader.printTo(JSON_Header);

    JBuffer.clear();

    count = payload.size();
    
}

void sendPower(){
  
    if (client.connect(server, 80)) {

    makeJSON();
        
    getToken();

    Serial.print("Header: ");
    Serial.println(JSON_Header);
    Serial.print("Hash: ");
    Serial.println(Hash_SHA256);

    String _POST = "POST /REST_API/CODE/public/API/" + String(channelname) + "/" + String(count) + " HTTP/1.1";

    Serial.println(_POST);
    client.println(_POST);
    client.println("Host: 192.168.0.29");
    client.println("Connection: keep-alive");
    client.println("x-auth-type: Signature");
    client.println("x-auth-alg: HS256");
    client.println("x-auth-timestamp: "+String(currenttime));
    client.println("x-auth-hash: "+Hash_SHA256);
    client.println("Accept: */*");
    client.println("Content-Length: "+ String(JSON_Payload.length()));
    client.println("Content-Type: application/x-www-form-urlencoded");
    client.println("User-Agent: ESP8266");

    client.println();

    Serial.println(JSON_Payload);
    client.println(JSON_Payload);
    delay(200);

    //Get Header from Server and check HTTP Response
    while (client.available())
      {
        String line = client.readStringUntil('\n');
        Serial.println(line);
        if (line.startsWith("200 OK", 9)){
          Serial.println("Positive feedback from Server");
        }
        else if (line.startsWith("401 Unauthorized", 9)){
          Serial.println("Nicht autorisiert");
        }
      }
    }

    client.stop();
   

}

void getToken(){

    String PreHash = JSON_Header + "." + JSON_Payload;

    int Length_PreHash = PreHash.length()+1;
    char prehash_char[Length_PreHash];
    PreHash.toCharArray(prehash_char, Length_PreHash);

    Hash_SHA256 = sha256(prehash_char, apiKey);
    
    Serial.print("Hash Input: ");
    Serial.println(prehash_char);
}

long unsigned int getTime(){

  timeClient.update();

  long unsigned int timeVal = timeClient.getEpochTime();
  Serial.println(timeVal);
  return timeVal;

}

String sha256(const char *SHA256_PAYLOAD_, String SHA256_API_KEY_){
  String RETURN_HASH;
  
  int SHA256_API_KEY_LENGTH_ = SHA256_API_KEY_.length();
  byte SHA256_API_KEY_BYTE_[SHA256_API_KEY_LENGTH_+1];
  SHA256_API_KEY_.getBytes(SHA256_API_KEY_BYTE_, SHA256_API_KEY_LENGTH_+1);
  
  SHA256HMAC hmac(SHA256_API_KEY_BYTE_, SHA256_API_KEY_LENGTH_);
  
  hmac.doUpdate(SHA256_PAYLOAD_, strlen(SHA256_PAYLOAD_));
  byte authCode[SHA256HMAC_SIZE];
  hmac.doFinal(authCode);
  
  /* authCode now contains our 32 byte authentication code */
  for (byte i = 0; i < SHA256HMAC_SIZE; i++)
  {
      if (authCode[i]<0x10) { RETURN_HASH +=0; }
      RETURN_HASH += String(authCode[i], HEX);
  }
  
  return RETURN_HASH;
}
