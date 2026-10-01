<?php

// Config
$config   = pwConfig::load('pwfaq');
$settings = $config['content'];
$defaults = $config['defaults'];

// The block's settings (drawer), else their start values (Project Wizard)
$sectionLayout = $block->sectionlayout()->or($defaults['section-layout'] ?? 'stacked')->value();
$faqStyle      = $block->faqstyle()->or($defaults['faq-style'] ?? 'lines')->value();
$behavior      = $block->faqbehavior()->or($defaults['faq-behavior'] ?? 'multiple')->value();
$firstOpen     = $block->faqfirstopen()->or($defaults['faq-first-open'] ?? 'no')->value() === 'yes';
$offsetAlign   = $defaults['item-offset-align'] ?? 'top';

// The look (Project Wizard → Design)
$icon         = $defaults['item-icon'] ?? 'chevron';
$iconPosition = $defaults['item-icon-position'] ?? 'right';
$iconStroke   = ['thin' => 1, 'normal' => 1.5, 'bold' => 2.5][$defaults['item-icon-stroke'] ?? 'normal'] ?? 1.5;

// The icon: its drawing from the icon choice; a plus turns into a minus
// when open, the others turn down
$iconSvg = '';
foreach ($config['layout']['item-icon']['options'] ?? [] as $option) {
	if (is_array($option) && ($option['value'] ?? null) === $icon && $icon !== 'none') {
		$iconSvg = '<svg viewBox="0 0 24 24" aria-hidden="true">' . ($option['svg'] ?? '') . '</svg>';
	}
}
$iconKind = in_array($icon, ['plus', 'circle-plus'], true) ? 'plus' : 'turn';

// An answer as HTML: the writer's as it is, plain text masked with its
// line breaks
$answerHtml = function ($item): string {
	$raw  = $item->answer()->value();
	$data = ($raw === null || $raw === '') ? [] : (json_decode($raw, true) ?? []);
	$mode = $data['mode'] ?? 'textarea';
	$text = (string) ($data[$mode] ?? '');
	if ($text === '') return '';
	return $mode === 'writer' ? $text : nl2br(esc($text), false);
};

// Custom Background
pwSnippet::customCss($block);

// Section + Grid open
echo pwSnippet::sectionOpen('faq', $block, $settings, ' data-layout-style="' . $sectionLayout . '" data-offset-align="' . $offsetAlign . '"');
echo pwSnippet::gridOpen($block);

// Split layout: intro (tagline, heading, text) in its own column next to the questions
if ($sectionLayout === 'split') echo '<div data-block="intro">' . "\n";

if (!empty($settings['tagline'])) snippet('tagline', ['content' => $block]);
if (!empty($settings['heading'])) snippet('heading', ['content' => $block]);
if (!empty($settings['editor']))  snippet('editor', ['content' => $block]);

if ($sectionLayout === 'split') echo '</div>' . "\n";

// Questions
$items = $block->blocks()->toBlocks()->filter(fn($item) => $item->question()->isNotEmpty());
if ($items->count() > 0):

	echo '<div data-block="items"';
	echo ' data-style="' . $faqStyle . '"';
	echo ' data-behavior="' . $behavior . '"';
	echo ' data-icon="' . ($iconSvg === '' ? 'none' : $icon) . '"';
	echo ' data-icon-kind="' . $iconKind . '"';
	echo ' style="--pw-faq-stroke:' . $iconStroke . '"';
	echo ' data-icon-position="' . $iconPosition . '"';
	echo ' data-icon-align="' . ($defaults['item-icon-align'] ?? 'center') . '"';
	echo ' data-divider="' . ($defaults['item-divider'] ?? 'enabled') . '"';
	echo ' data-answer-width="' . ($defaults['item-answer-width'] ?? 'text') . '"';
	echo ' data-shape="' . ($defaults['item-shape'] ?? 'custom') . '"';
	echo '>' . "\n";

	// one at a time: the browser closes the others (details with one name)
	$group = 'faq-' . $block->id();
	$jsonLd = [];

	foreach ($items->values() as $index => $item):
		$question = esc($item->question()->value());
		$answer   = $answerHtml($item);
		$open     = $behavior === 'open' || ($firstOpen && $index === 0);

		if ($behavior === 'open'):
			// always open: no folding, the question as a heading
			echo '<div data-block="item">' . "\n";
			echo '<div data-field="summary" data-entry><h3 data-field="heading">' . $question . '</h3></div>' . "\n";
		else:
			echo '<details data-block="item"';
			e($behavior === 'single', ' name="' . $group . '"');
			e($open, ' open');
			echo '>' . "\n";
			echo '<summary data-field="summary" data-entry>';
			echo '<h3 data-field="heading">' . $question . '</h3>';
			e($iconSvg !== '', '<span data-field="icon">' . $iconSvg . '</span>');
			echo '</summary>' . "\n";
		endif;

		if ($answer !== ''):
			echo '<div data-field="answer" data-entry><div data-field="text">' . $answer . '</div></div>' . "\n";
		endif;

		echo $behavior === 'open' ? '</div>' . "\n" : '</details>' . "\n";

		// for search engines: the question and its answer
		$jsonLd[] = [
			'@type' => 'Question',
			'name'  => $item->question()->value(),
			'acceptedAnswer' => [
				'@type' => 'Answer',
				'text'  => $answer,
			],
		];
	endforeach;

	echo '</div>' . "\n"; // End Items

	// Structured data (Schema.org FAQPage)
	echo '<script type="application/ld+json">' . json_encode([
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $jsonLd,
	], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . '</script>' . "\n";
endif;

// Close
echo pwSnippet::gridClose();
echo pwSnippet::sectionClose();
