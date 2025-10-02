<?php
use Ksfraser\PhpabcCanntaireachd\AbcComment;
use Ksfraser\PhpabcCanntaireachd\CommentSyntax;
use PHPUnit\Framework\TestCase;

class AbcCommentTest extends TestCase {
    public function testFullLineCommentRendersWithCommentChar() {
        $comment = new AbcComment('This is a comment', false);
        $this->assertEquals(CommentSyntax::COMMENT_CHAR . 'This is a comment' . "\n", $comment->render());
    }

    public function testInlineCommentRendersWithCommentChar() {
        $comment = new AbcComment('Inline comment', true);
        $this->assertEquals(CommentSyntax::COMMENT_CHAR . 'Inline comment' . "\n", $comment->render());
    }

    public function testGetTextAndIsInline() {
        $comment = new AbcComment('abc', true);
        $this->assertEquals('abc', $comment->getText());
        $this->assertTrue($comment->isInline());
        $comment2 = new AbcComment('xyz', false);
        $this->assertEquals('xyz', $comment2->getText());
        $this->assertFalse($comment2->isInline());
    }
}
