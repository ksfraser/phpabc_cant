/**
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Class AbcLine
 *
 * Represents a line of ABC music, containing bars and supporting translation and rendering of canntaireachd lyrics.
 *
 * @uml
 * @startuml
 * class AbcLine {
 *   - headerLine: string
 *   - bars: array
 *   + translateBars(translator)
 *   + renderCanntLyricLine(): string
 *   + add(item: AbcItem)
 *   + getBars(): array
 * }
 * AbcLine --> AbcBar : contains
 * AbcLine --> AbcItem : contains
 * @enduml
 *
 * @sequence
 * @startuml
 * participant User
 * participant AbcLine
 * participant AbcBar
 * participant Translator
 * User -> AbcLine: add(bar)
 * User -> AbcLine: translateBars(translator)
 * AbcLine -> AbcBar: translateNotes(translator)
 * AbcBar --> AbcLine: (translated)
 * User -> AbcLine: renderCanntLyricLine()
 * AbcLine -> AbcBar: renderCanntaireachd()
 * AbcBar --> AbcLine: cannt
 * AbcLine --> User: w: ...
 * @enduml
 *
 * @flowchart
 * @startuml
 * start
 * :Add bars to line;
 * :Translate all bars using translator;
 * :Render canntaireachd lyric line;
 * stop
 * @enduml
 */
class AbcLine extends AbcItem {

    /**
     * Translate all bars in this line using the provided translator (DI, SRP, DRY).
     * @param object $translator Any AbcTokenTranslator subclass
     */
    public function translateBars($translator) {
        file_put_contents('debug.log', "AbcLine::translateBars: called\n", FILE_APPEND);
        foreach ($this->bars as $i => $bar) {
            $barClass = get_class($bar);
            $hasTranslateNotes = method_exists($bar, 'translateNotes') ? 'yes' : 'no';
            file_put_contents('debug.log', "AbcLine::translateBars: bar $i class=$barClass method_exists(translateNotes)=$hasTranslateNotes\n", FILE_APPEND);
            if (!($bar instanceof \Ksfraser\PhpabcCanntaireachd\Contract\RenderableCanntaireachdInterface)) {
                file_put_contents('debug.log', "AbcLine::translateBars: bar $i does not implement RenderableCanntaireachdInterface\n", FILE_APPEND);
                throw new \LogicException('Bar does not implement RenderableCanntaireachdInterface');
            }
            if (!method_exists($bar, 'translateNotes')) {
                file_put_contents('debug.log', "AbcLine::translateBars: bar $i does not implement translateNotes\n", FILE_APPEND);
                throw new \LogicException('Bar does not implement translateNotes');
            }
            file_put_contents('debug.log', "AbcLine::translateBars: calling translateNotes on bar $i\n", FILE_APPEND);
            $bar->translateNotes($translator);
        }
    }

    /**
     * Render the canntaireachd lyric (w:) line for this line, using canntaireachd from each bar/note.
     * @return string
     */
    public function renderCanntLyricLine(): string {
        $wTokens = [];
        foreach ($this->bars as $bar) {
            if (!($bar instanceof \Ksfraser\PhpabcCanntaireachd\Contract\RenderableCanntaireachdInterface)) {
                throw new \LogicException('Bar does not implement RenderableCanntaireachdInterface');
            }
            $cannt = trim($bar->renderCanntaireachd());
            if ($cannt !== '') {
                $wTokens[] = $cannt;
            }
        }
        if (empty($wTokens)) return '';
        return 'w: ' . implode(' ', $wTokens);
    }
    protected $headerLine = '';
    protected $bars = [];

    public function setHeaderLine($line) {
        $this->headerLine = $line;
    }
    public function getBars() {
        return $this->bars;
    }

    // Override add() to handle AbcBar objects specially
    public function add(AbcItem $item) {
        // Accept both possible AbcBar namespaces for compatibility
        if ($item instanceof \Ksfraser\PhpabcCanntaireachd\AbcBar || $item instanceof \Ksfraser\PhpabcCanntaireachd\Tune\AbcBar) {
            $this->bars[] = $item;
        } else {
            parent::add($item);
        }
    }
    protected function renderSelf(): string {
        $out = '';
        if ($this->headerLine) {
            $out .= rtrim($this->headerLine, "\n") . "\n";
        }
        if (!empty($this->bars)) {
            $barStrs = [];
            foreach ($this->bars as $barObj) {
                $barContent = '';
                if (method_exists($barObj, 'renderSelf')) {
                    $barContent = trim($barObj->renderSelf());
                } else {
                    $barContent = trim((string)$barObj);
                }
                // Don't add | for comments or instructions
                if (preg_match('/^%%/', $barContent) || preg_match('/^%/', $barContent)) {
                    $out .= $barContent . "\n";
                } else {
                    $barStrs[] = $barContent;
                }
            }
            if (!empty($barStrs)) {
                $out .= '|' . implode('|', $barStrs) . "|\n";
            }
        }
        // Only add a blank line if this is a true blank (no header, no bars)
        if (!$this->headerLine && empty($this->bars)) {
            $out .= "\n";
        }
        return $out;
    }
    public function hasContent(): bool {
        return !empty($this->headerLine) || !empty($this->bars);
    }
    // Add line-level sanity checks here
}
