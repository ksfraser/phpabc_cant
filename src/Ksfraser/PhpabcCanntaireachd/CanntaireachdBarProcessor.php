<?php
namespace Ksfraser\PhpabcCanntaireachd;


require_once __DIR__ . '/TokenMappingHelpers.php';
use Ksfraser\PhpabcCanntaireachd\Exceptions\TokenMappingException;

class CanntaireachdBarProcessor
{
    protected $mapper;

    public function __construct($dictionary)
    {
        $this->mapper = new TokenToCanntMapper($dictionary);
    }

    /**
     * Convert an array of ABC tokens to a canntaireachd string.
     * @param array $tokens
     * @return string
     */
    public function tokensToCanntaireachd(array $tokens): string
    {
        $canntArr = [];
        foreach ($tokens as $token) {
            if (trim($token) === '' || $token === '|' || $token === '||' || $token === '|:' || $token === ':') {
                continue;
            }
            try {
                $canntArr[] = $this->mapper->map($token);
            } catch (TokenMappingException $e) {
                // Optionally log or skip unmapped tokens
                // error_log($e->getMessage());
            }
        }
        return implode(' ', $canntArr);
    }
}
