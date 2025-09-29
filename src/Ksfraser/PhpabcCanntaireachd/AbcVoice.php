<?php
/**
 * Class AbcVoice
 *
 * Represents a voice in ABC notation.
 *
 * @package Ksfraser\PhpabcCanntaireachd
 */
namespace Ksfraser\PhpabcCanntaireachd;
use ksfraser\origin\Origin;
class AbcVoice extends Origin {
	/**
	 * Parse a block of lines for a voice, splitting into bars and delegating to AbcBar.
	 * @param array $lines
	 * @param string $voiceId
	 * @return AbcVoice
	 */
	public static function parse($lines, $voiceId, $context = [])
	{
		$voice = new self();
		$voice->bars = [];
		$voice->header = null;
		$ctxMgr = new \Ksfraser\PhpabcCanntaireachd\ContextManager($context);
		foreach ($lines as $line) {
			$trimmed = trim($line);
			$ctxMgr->applyToken($trimmed);
			if (preg_match('/^V:/', $trimmed)) {
				$voice->header = new \Ksfraser\PhpabcCanntaireachd\Header\AbcHeaderV($line);
				continue;
			}
			$bars = preg_split('/\|/', $line);
			foreach ($bars as $barText) {
				$barText = trim($barText);
				if ($barText !== '') {
					$bar = \Ksfraser\PhpabcCanntaireachd\Tune\AbcBar::parse($barText, $ctxMgr->getAll());
					$voice->bars[] = $bar;
				}
			}
		}
		$voice->voiceId = $voiceId;
		return $voice;
	}
}
