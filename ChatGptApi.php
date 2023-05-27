<?php
class ChatGptApi
{
    private $api_key;
    private $url = "https://api.openai.com/v1/chat/completions";

    function __construct($api_key)
    {
        $this->api_key = $api_key;
    }

    public function run($question)
    {
        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Bearer " . $this->api_key;

        $postData = array(
            'model' => "gpt-3.5-turbo",
            'messages' => [
                ["role" => "user", "content" => $question . ' Summarize the most important contents in one line and show the less important contents in bullet points in japanese']
            ], // role: system / user
            'temperature' => 0.7 // low:accuracy↑,diversity↓,hight:accuracy↓,diversity↑
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // if true string, false status
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $result = curl_exec($ch);

        if ($result === false) {
            throw new Exception(curl_error($ch));
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // if ChatGPT Error return 401
        if ($http_code !== 200) {
            throw new Exception('HTTP Error');
        }

        curl_close($ch);

        $json = json_decode($result, true);

        if (!isset($json["choices"][0]["message"]["content"])) {
            throw new Exception('Unexpected response format');
        }

        return $json["choices"][0]["message"]["content"];
    }
}
