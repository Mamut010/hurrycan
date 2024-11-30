<?php
namespace App\Core\Template;

use App\Core\Template\Contracts\TemplateParser;

class HurrycanTemplateParser implements TemplateParser
{
    #[\Override]
    public function parse(string $template): string {
        $template = $this->discardComments($template);
        $template = $this->parseCodeBlock($template);
        $template = $this->parseStatements($template);
        $template = $this->parseControlStructures($template);
        $template = $this->parseSpecialTokens($template);
        return $template;
    }

    private function discardComments(string $template) {
        $commentPattern = '/{{--.*?--}}/s';
        return preg_replace(
            $commentPattern,
            '',
            $template
        );
    }

    private function parseCodeBlock(string $template) {
        $codeBlockPattern = '/@php(.*?)@endphp/s';
        return preg_replace(
            $codeBlockPattern,
            '<?php $1 ?>',
            $template
        );
    }

    private function parseStatements(string $template) {
        $echoPattern = '/{{\s*(.+?)\s*}}/';
        $echoRawPattern = '/{!!\s*(.+?)\s*!!}/';
        $template = preg_replace(
            $echoPattern,
            "<?php echo htmlspecialchars(strval($1)); ?>",
            $template
        );
        $template = preg_replace(
            $echoRawPattern,
            "<?php echo $1; ?>",
            $template
        );
        return $template;
    }

    private function parseControlStructures(string $template) {
        $template = $this->parseIf($template);
        $template = $this->parseUnless($template);
        $template = $this->parseEmpty($template);
        $template = $this->parseSwitch($template);
        $template = $this->parseFor($template);
        $template = $this->parseWhile($template);
        $template = $this->parseForeach($template);
        return $template;
    }

    private function parseIf(string $template) {
        $ifPattern = '/@if\s*\(\s*(.+)\s*\)/';
        $elseifPattern = '/@elseif\s*\(\s*(.+)\s*\)/';
        $elsePattern = '/@else\s/';
        $endifDirective = '@endif';
        $template = preg_replace(
            $ifPattern,
            '<?php if ($1): ?>',
            $template
        );
        $template = preg_replace(
            $elseifPattern,
            '<?php elseif ($1): ?>',
            $template
        );
        $template = preg_replace(
            $elsePattern,
            '<?php else: ?>',
            $template
        );
        $template = $this->parseAsEndif($template, $endifDirective);
        return $template;
    }

    private function parseUnless(string $template) {
        $unlessPattern = '/@unless\s*\(\s*(.+)\s*\)/';
        $endunlessDirective = '@endunless';
        $template = preg_replace(
            $unlessPattern,
            '<?php if (!($1)): ?>',
            $template
        );
        $template = $this->parseAsEndif($template, $endunlessDirective);
        return $template;
    }

    private function parseEmpty(string $template) {
        $emptyPattern = '/@empty\s*\(\s*(.+)\s*\)/';
        $endemptyDirective = '@endempty';
        $template = preg_replace(
            $emptyPattern,
            '<?php if (empty($1)): ?>',
            $template
        );
        $template = $this->parseAsEndif($template, $endemptyDirective);
        return $template;
    }

    private function parseAsEndif(string $template, string $directive) {
        return str_replace(
            $directive,
            '<?php endif; ?>',
            $template
        );
    }

    private function parseSwitch(string $template) {
        $switchPattern = '/@switch\s*\(\s*(.+)\s*\)/';
        $casePattern = '/\s*@case\s*\(\s*(.+)\s*\)/';
        $defaultPattern= '/\s*@default/';
        $endswitchPattern = '/\s*@endswitch/';
        $template = preg_replace(
            $switchPattern,
            '<?php switch ($1): ?>',
            $template
        );
        $template = preg_replace(
            $casePattern,
            '<?php case $1: ?>',
            $template
        );
        $template = preg_replace(
            $defaultPattern,
            '<?php default: ?>',
            $template
        );
        $template = preg_replace(
            $endswitchPattern,
            '<?php endswitch; ?>',
            $template
        );
        return $template;
    }

    private function parseFor(string $template) {
        $forPattern = '/@for\s*\(\s*(.+)\s*\)/';
        $endforPattern = '/@endfor\s/';
        $template = preg_replace(
            $forPattern,
            '<? for ($1): ?>',
            $template
        );
        $template = preg_replace(
            $endforPattern,
            '<? endfor; ?>',
            $template
        );
        return $template;
    }

    private function parseWhile(string $template) {
        $whilePattern = '/@while\s*\(\s*(.+)\s*\)/';
        $endwhilePattern = '/@endwhile\s/';
        $template = preg_replace(
            $whilePattern,
            '<? while ($1): ?>',
            $template
        );
        $template = preg_replace(
            $endwhilePattern,
            '<? endwhile; ?>',
            $template
        );
        return $template;
    }

    private function parseForeach(string $template) {
        $foreachPattern = '/@foreach\s*\(\s*(.+)\s*\)/';
        $endforeachPattern = '/@endforeach\s/';
        $template = preg_replace(
            $foreachPattern,
            '<?php $index = 0; $count = 1; $even = true; $odd = false; foreach ($1): ?>',
            $template
        );
        $template = preg_replace(
            $endforeachPattern,
            '<?php $index++; $count++; $even = !$even; $odd = !$odd; endforeach; ?>',
            $template
        );
        return $template;
    }

    private function parseSpecialTokens(string $template){
        $breakDirective = '@break';
        $continueDirective = '@continue';
        $capturedQuotedString = '(\'[^\']++\'|"[^"]++")';
        $usePattern = "/@use\\s*\(\\s*$capturedQuotedString\\s*(,\\s*$capturedQuotedString\\s*)?\)/";
        $template = str_replace(
            $breakDirective,
            '<?php break; ?>',
            $template
        );
        $template = str_replace(
            $continueDirective,
            '<?php continue; ?>',
            $template
        );
        $template = preg_replace_callback($usePattern, function ($matches) {
            $used = trim($matches[1], "'\"");
            $output = "<?php use $used";
            if (isset($matches[3])) {
                $as = trim($matches[3], "'\"");
                $output = $output . ' as ' . $as;
            }
            return $output . '; ?>';
        }, $template);
        return $template;
    }
}
