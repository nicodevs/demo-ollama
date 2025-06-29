<?php

use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Artisan;

Artisan::command('ollama', function () {
    $context = file_get_contents(base_path('store.txt'));

    $systemPrompt = "You are a chatbot for a comic book store. You MUST ONLY answer using the data in the context below. If the answer is not found in the context, reply exactly: 'Sorry, I do not have info about that.' Do NOT guess or infer. Do NOT use outside knowledge. Do NOT elaborate. Do NOT list comics or products not present in the context. Keep responses short and helpful.\n\nOnly trust what is explicitly listed. If it’s not in the context, assume we don’t have it.\n\nCONTEXT: $context";

    $messages = [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => 'What is your phone?'],
        ['role' => 'assistant', 'content' => 'You can call us at (555) 392-4765'],
        ['role' => 'user', 'content' => 'Do you sell snacks?'],
        ['role' => 'assistant', 'content' => 'Sorry, I do not have info about that.'],
    ];

    while (true) {
        $question = trim(readline("You: "));
        if (strtolower($question) === 'exit') {
            break;
        }
        $messages[] = ['role' => 'user', 'content' => $question];

        $response = Ollama::model('tinyllama:latest')
            ->options(['temperature' => 0])
            ->stream(true)
            ->chat($messages);

        $answer = '';
        Ollama::processStream($response->getBody(), function($data) use (&$answer) {
            $answer .= $data['message']['content'];
            echo $data['message']['content'];
            flush();
        });
        echo "\n";
        $messages[] = ['role' => 'assistant', 'content' => $answer];
    }
});
