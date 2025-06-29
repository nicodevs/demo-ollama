<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Cloudstudio\Ollama\Facades\Ollama;

class SuperSpyAssistant extends Command
{
    protected $signature = 'superspy';
    protected $description = 'Start an interactive chat session.';

    public function handle()
    {
        $messages = [['role' => 'system', 'content' => File::get(base_path('system.txt'))]];
        $this->info('This is MINDY. What do you want to know, agent? Type "exit" to quit.');

        while (true) {
            $input = $this->ask('Your message');
            if (strtolower($input) === 'exit') break;

            $messages[] = ['role' => 'user', 'content' => $input];

            $answer = '';
            $stream = Ollama::model('llama3.1')
                // ->options(['temperature' => 0])
                ->stream(true)
                ->chat($messages);

            Ollama::processStream($stream->getBody(), function ($data) use (&$answer) {
                echo $data['message']['content'];
                flush();
                $answer .= $data['message']['content'];
            });

            $this->newLine(2);
            $messages[] = ['role' => 'assistant', 'content' => $answer];
        }
    }
}
