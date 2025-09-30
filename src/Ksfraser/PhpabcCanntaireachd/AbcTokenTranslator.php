<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Abstract class AbcTokenTranslator
 *
 * Provides a base for all ABC token translators (e.g., ABC->Cannt, ABC->BMW, BMW->Cannt, etc).
 * Enforces DI for the dictionary and SRP for translation direction.
 *
 * @package Ksfraser\PhpabcCanntaireachd
 *
 * @uml
 * @startuml
 * abstract class AbcTokenTranslator {
 *   - dictionary: TokenDictionary
 *   + __construct(dictionary: TokenDictionary)
 *   + translate(token): string|null
 * }
 * AbcTokenTranslator <|-- BagpipeAbcToCanntTranslator
 * AbcTokenTranslator <|-- (other translators)
 * class TokenDictionary
 * @enduml
 *
 * @sequence
 * @startuml
 * participant User
 * participant AbcTokenTranslator
 * participant TokenDictionary
 * User -> AbcTokenTranslator: translate(token)
 * AbcTokenTranslator -> TokenDictionary: ... (translation logic)
 * TokenDictionary --> AbcTokenTranslator: ...
 * AbcTokenTranslator --> User: result
 * @enduml
 *
 * @flowchart
 * @startuml
 * start
 * :Receive token and dictionary;
 * :Subclasses implement translate(token);
 * :Use dictionary for mapping;
 * :Return result;
 * stop
 * @enduml
 */
abstract class AbcTokenTranslator {
    /**
     * @var TokenDictionary
     */
    protected $dictionary;

    /**
     * @param TokenDictionary $dictionary
     */
    public function __construct(TokenDictionary $dictionary) {
        $this->dictionary = $dictionary;
    }

    /**
     * Translate a token (to be implemented by subclasses).
     * @param mixed $token
     * @return string|null
     */
    abstract public function translate($token);
}
