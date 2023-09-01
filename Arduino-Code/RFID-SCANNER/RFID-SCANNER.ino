#include <rdm6300.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266WiFi.h>        // Include the Wi-Fi library

#define RDM6300_RX_PIN  14// read the SoftwareSerial doc above! may need to change this pin to 10...


const char* ssid     = "YOUR_SSID";         // The SSID (name) of the Wi-Fi network you want to connect to
const char* password = "YOUR_PASSWORD";     // The password of the Wi-Fi network
const char* server_address = "YOUR_PROJECT_URL";
WiFiClient client;

Rdm6300 rdm6300;

void setup()
{
	Serial.begin(9600);

    WiFi.begin(ssid, password);             // Connect to the network
  Serial.print("Connecting to ");
  Serial.print(ssid); Serial.println(" ...");

  int i = 0;
  while (WiFi.status() != WL_CONNECTED) { // Wait for the Wi-Fi to connect
    delay(1000);
 Serial.print('.');
  }

  Serial.println('\n');
  Serial.println("Connection established!");  
  Serial.print("IP address:\t");
  Serial.println(WiFi.localIP());         // Send the IP address of the ESP8266 to the computer


	rdm6300.begin(RDM6300_RX_PIN);

	Serial.println("\nPlace RFID tag near the rdm6300...");
}

void loop()
{
	/* get_new_tag_id returns the tag_id of a "new" near tag,
	following calls will return 0 as long as the same tag is kept near. */
	if (rdm6300.get_new_tag_id()) {
		String data = "data:" + rdm6300.get_tag_id();

		HTTPClient http;    //Declare object of class HTTPClient
http.setTimeout(10000); // set timeout to 10 seconds
int httpCode = https.POST("station:1; id: "+rdm6300.get_tag_id());   //Send the request
if (httpCode > 0) {
    String payload = http.getString();                  //Get the response payload
    Serial.println(httpCode);   //Print HTTP return code
    Serial.println(payload);    //Print request response payload
} else {
    Serial.println("Error sending request: " + http.errorToString(httpCode));
}

	/* get_tag_id returns the tag_id as long as it is near, 0 otherwise. */

	delay(10);
  }}