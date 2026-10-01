<?php

// the options a project allows, as toggles (its own labels)
$faqOptions = function (array $cfg, string $key, array $all, string $labelPrefix): array {
	$allowed = $cfg['style'][$key]['options'] ?? $all;
	return array_values(array_map(
		fn($v) => ['value' => $v, 'text' => ['*' => $labelPrefix . '.' . $v]],
		array_values(array_intersect($all, $allowed))
	));
};

// a block setting with its start value: toggles, or – only one option
// allowed – a hidden field holding it
$faqSetting = function (array $options, string $label, $default, array $extra = []): array {
	if (count($options) <= 1) {
		return ['type' => 'hidden', 'default' => $default];
	}
	return array_merge([
		'label'   => $label,
		'type'    => 'toggles',
		'default' => $default,
		'options' => $options,
	], $extra);
};

return [

	/* ============================================================================
	   Main Block
	============================================================================ */

	'blocks/pwfaq' => pwBlueprint::main('pwfaq', function ($cfg) use ($faqOptions, $faqSetting) {
		$defaults = $cfg['defaults'];

		$layouts   = $faqOptions($cfg, 'section-layout', ['stacked', 'split'], 'kirbyblock-faq.section-layout');
		$styles    = $faqOptions($cfg, 'faq-style', ['lines', 'cards'], 'kirbyblock-faq.faq-style');
		$behaviors = $faqOptions($cfg, 'faq-behavior', ['multiple', 'single', 'open'], 'kirbyblock-faq.faq-behavior');
		$firstOpen = $faqOptions($cfg, 'faq-first-open', ['no', 'yes'], 'kirbyblock-faq.faq-first-open');

		return [
			'name' => 'kirbyblock-faq.name',
			'icon' => 'faq',
			'contentFields' => array_merge(
				pwBlueprint::stdContent($cfg, ['tagline', 'heading', 'editor']),
				[
					'blocks' => [
						'extends'   => 'pagewizard/fields/blocks',
						'label'     => 'kirbyblock-faq.items',
						'fieldsets' => ['pwfaqitem'],
					],
				]
			),
			'styleExtras' => [
				// the intro above the questions or beside them
				'sectionLayout' => $faqSetting($layouts, 'kirbyblock-faq.section-layout', $defaults['section-layout'] ?? 'stacked', [
					'help' => 'kirbyblock-faq.section-layout.help',
				]),
				// lines between the questions, or each question a card
				'faqStyle' => $faqSetting($styles, 'kirbyblock-faq.faq-style', $defaults['faq-style'] ?? 'lines'),
				// several open, one at a time, or always open
				'faqBehavior' => $faqSetting($behaviors, 'kirbyblock-faq.faq-behavior', $defaults['faq-behavior'] ?? 'multiple'),
				// the first question open at the start (folding questions only –
				// Kirby's "when" cannot say "multiple or single")
				'faqFirstOpen' => $faqSetting($firstOpen, 'kirbyblock-faq.faq-first-open', $defaults['faq-first-open'] ?? 'no', [
					'help' => 'kirbyblock-faq.faq-first-open.help',
				]),
			],
		];
	}),

	/* ============================================================================
	   Item Blueprint: a question and its answer
	============================================================================ */

	'blocks/pwfaqitem' => function () {
		$config       = pwConfig::load('pwfaq');
		$fields       = $config['fields'];
		$editor       = $config['editor'];
		$settings     = $config['content'];
		$fieldOptions = $config['field-options'];

		// the answer: the project's editor (text field and/or writer)
		$answerSettings = array_merge($settings, ['editor' => $settings['item-editor'] ?? ['writer']]);
		$answer = pwEditor::contentField($editor, $answerSettings);
		$answer['label']        = 'kirbyblock-faq.item.answer';
		$answer['align']        = $fields['align-item-editor'] ?? $fields['align-editor'] ?? null;
		$answer['size']         = $fields['size-item-editor'] ?? $fields['size-editor'] ?? null;
		$answer['alignOptions'] = $fieldOptions['item-editor']['align'] ?? $fieldOptions['editor']['align'] ?? null;
		$answer['sizeOptions']  = $fieldOptions['item-editor']['sizes'] ?? $fieldOptions['editor']['sizes'] ?? null;
		$answer['defaultMode']  = $fields['mode-item-editor'] ?? $fields['mode-editor'] ?? null;

		return [
			'name' => 'kirbyblock-faq.item',
			'icon' => 'faq',
			'fields' => [
				'question' => [
					'type'        => 'text',
					'label'       => 'kirbyblock-faq.item.question',
					'placeholder' => 'kirbyblock-faq.item.question.placeholder',
					'required'    => true,
				],
				'answer' => $answer,
			],
		];
	},
];
