<?php
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Class ContextManager
 *
 * Manages ABC parsing context (key, meter, length, voice, etc.) and applies context changes.
 */
class ContextManager {
    protected $context = [
        'voice' => null,
        'key' => null,
        'meter' => null,
        'length' => null,
    ];

    public function __construct($initial = []) {
        foreach ($initial as $k => $v) {
            $this->context[$k] = $v;
        }
    }

    public function get($key) {
        return $this->context[$key] ?? null;
    }

    public function set($key, $value) {
        $this->context[$key] = $value;
    }

    public function getAll() {
        return $this->context;
    }

    /**
     * Apply context changes from a token (e.g., K:, M:, L:, V:)
     */
    public function applyToken($token) {
        $token = trim($token);
        if (preg_match('/^K:(.*)$/', $token, $m)) {
            $this->set('key', $m[1]);
            return true;
        }
        if (preg_match('/^M:(.*)$/', $token, $m)) {
            $this->set('meter', $m[1]);
            return true;
        }
        if (preg_match('/^L:(.*)$/', $token, $m)) {
            $this->set('length', $m[1]);
            return true;
        }
        if (preg_match('/^(?:\[)?V:([^\s\]]+)(?:\])?/', $token, $m)) {
            $this->set('voice', $m[1]);
            return true;
        }
        return false;
    }
}
