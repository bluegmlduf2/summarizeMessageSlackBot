<?php
require_once 'config.php';
require_once 'ChatGptApi.php';

function main()
{
    if (empty($_POST)) {
        echo ('Request does not have values');
        return;
    }

    $post_data = json_decode($_POST['payload'], true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo ('Request data is not a valid JSON');
        return;
    }

    $question = $post_data['message']['text'];
    $response_url = $post_data['response_url'];

    try {
        $chatGptApi = new ChatGptApi(API_KEY);

        $answer = $chatGptApi->run($question);

        $send_data = json_encode(array(
            "text" => $answer
        ));

        $ch = curl_init($response_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $send_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $response = curl_exec($ch);

        // curl error handling
        if ($response === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
    } catch (Exception $e) {
        echo 'An error occurred';
    } finally {
        if (!empty($ch)) {
            curl_close($ch);
        }
    }
}

main();
