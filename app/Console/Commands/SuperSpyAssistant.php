<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Cloudstudio\Ollama\Facades\Ollama;

class SuperSpyAssistant extends Command
{
    protected $signature = 'mindy';
    protected $description = 'Shhh, top secret!';

    public function handle()
    {
        // Show the welcome message
        $this->info('This is MINDY. What do you want to know, agent? Type "exit" to quit.');

        // Load the system prompt
        $messages = [['role' => 'system', 'content' => File::get(base_path('system.txt'))]];

        // Start the chat loop
        while (true) {
            // Get user input
            $input = $this->ask('Your message');

            // Check for exit command
            if (strtolower($input) === 'exit') break;

            // Add user input to messages
            $messages[] = ['role' => 'user', 'content' => $input];

            // Call the Ollama model and stream the response
            $stream = Ollama::model('llama3.1')
                ->stream(true)
                ->chat($messages);

            // Process the streamed response
            $chunks = Ollama::processStream($stream->getBody(), function ($data) {
                // Print the response content
                echo $data['message']['content'];
                // Flush the output buffer to show the response in real-time
                flush();
            });

            // Add the answer to the messages
            $messages[] = ['role' => 'assistant', 'content' => implode('', array_column($chunks, 'response'))];

            // Print line breaks for better readability
            $this->newLine(2);
        }
    }
}
