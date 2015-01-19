<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 8/5/14
 * Time: 10:54 AM
 */
namespace bariew\moduleModule;
use Symfony\Component\Console\Output\StreamOutput;
class HtmlOutput extends StreamOutput
{
    public $messages = [];
    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        $this->messages = array_merge($this->messages, (array) $messages);
        parent::write($messages, $newline, $type);
    }
} 