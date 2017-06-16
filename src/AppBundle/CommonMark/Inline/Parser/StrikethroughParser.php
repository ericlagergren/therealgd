<?php

namespace Raddit\AppBundle\CommonMark\Inline\Parser;

use League\CommonMark\ContextInterface;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\InlineParserContext;
use Raddit\AppBundle\CommonMark\Inline\Element\Strikethrough;

/**
 * Seized from <https://github.com/uafrica/commonmark-ext> and modified to
 * support newer versions of the league/commonmark library.
 *
 * @author Johan Meiring <johan@uafrica.com>
 * @license MIT
 */
class StrikethroughParser extends AbstractInlineParser {
    /**
     * @return string[]
     */
    public function getCharacters() {
        return ['~'];
    }

    /**
     * @param ContextInterface|InlineParserContext $context
     *
     * @return bool
     */
    public function parse(InlineParserContext $context) {
        $cursor = $context->getCursor();
        $character = $cursor->getCharacter();

        if ($cursor->peek(1) !== $character) {
            return false;
        }

        $tildes = $cursor->match('/^~~+/');

        if ($tildes === '') {
            return false;
        }

        $previous_state = $cursor->saveState();

        while ($matching_tildes = $cursor->match('/~~+/m')) {
            if ($matching_tildes === $tildes) {
                $text = mb_substr($cursor->getLine(), $previous_state->getCurrentPosition(),
                    $cursor->getPosition() - $previous_state->getCurrentPosition() - strlen($tildes), 'utf-8');

                $text = preg_replace('/[ \n]+/', ' ', $text);

                $context->getContainer()->appendChild(new Strikethrough(trim($text)));

                return true;
            }
        }

        // If we got here, we didn't match a closing tilde pair sequence
        $cursor->restoreState($previous_state);

        $context->getContainer()->appendChild(new Text($tildes));

        return true;
    }
}
