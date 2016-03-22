<?php

class StreamReadProgressWrapper
{
    /** @var resource */
    public $context;
    /** @var StreamInterface */
    private $stream;

    const PROTOCOL_NAME = 'flysystemreadprogress';

    public static function ensureWrapperIsRegisered()
    {
        if ( ! in_array(self::PROTOCOL_NAME, stream_get_wrappers())) {
            stream_register_wrapper(self::PROTOCOL_NAME, __CLASS__);
        }
    }

    public static function wrapStream($stream, callable $listener)
    {
        static::ensureWrapperIsRegisered();

        $context = stream_context_create([
            self::PROTOCOL_NAME => [
                'stream' => $stream,
                'listener' => $listener,
            ],
        ]);

        return fopen(self::PROTOCOL_NAME . '://stream', fstat($stream)['mode'], null, $context);
    }

    public function stream_open($path, $mode, $flags, &$opened_path)
    {
        $options = stream_context_get_options($this->context);

        if ( ! isset($options[self::PROTOCOL_NAME])) {
            return false;
        }

        $this->listener = $options[self::PROTOCOL_NAME]['listener'];
        $this->stream = $options[self::PROTOCOL_NAME]['stream'];
        $this->size = fstat($this->stream)['size'];

        return true;
    }

    public function stream_close()
    {
        return fclose($this->stream);
    }

    public function stream_eof()
    {
        return feof($this->stream);
    }

    public function  stream_read($count)
    {
        $response =  fread($this->stream, $count);
        call_user_func($this->listener, $this->size, ftell($this->stream), $count);

        return $response;
    }

    public function stream_seek($offset , $whence = SEEK_SET)
    {
        return fseek($this->stream, $offset, $whence);
    }

    public function stream_stat()
    {
        return fstat($this->stream);
    }

    public function stream_tell()
    {
        return ftell($this->stream);
    }

    public function stream_truncate($size)
    {
        return ftruncate($this->stream, $size);
    }

    public function stream_write($data)
    {
        return fwrite($this->stream, $data);
    }
}