<?php
require __DIR__ . '/mail_transport.php';
define('ENV_LOADED', true);

// External Turnstile boundary; no request reaches Cloudflare.
class TestTurnstile {
    public $context;
    private $position = 0;
    public function stream_open($path, $mode, $options, &$openedPath) { return true; }
    public function stream_read($count) {
        $response = '{"success":' . (getenv('TEST_TURNSTILE') === 'pass' ? 'true' : 'false') . '}';
        $chunk = substr($response, $this->position, $count);
        $this->position += strlen($chunk);
        return $chunk;
    }
    public function stream_eof() { return $this->position > 0; }
    public function stream_stat() { return []; }
}
stream_wrapper_unregister('https');
stream_wrapper_register('https', TestTurnstile::class);
$_POST = json_decode(stream_get_contents(STDIN), true);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = getenv('TEST_IP');
require __DIR__ . '/../../public/' . ($argv[1] === 'contact' ? 'contact' : 'quote-request') . '.php';
