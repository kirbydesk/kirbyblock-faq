<template>
	<!-- the block in the panel: its info bar and the shared preview of the
	     Project Wizard (the project's look, the real questions); a double
	     click opens the block -->
	<div class="pwPreview" data-kirbyblock="faq" @dblclick="open">

		<pwBlockinfo
			:value="$t('kirbyblock-faq.name')"
			:design="'pwfaq'"
			icon="faq"
		/>

		<pw-block-panel-preview type="pwfaq" :content="content" />

	</div>
</template>

<script>
import pwBlockinfo from '@/../../kirby-pagewizard/src/components/blockinfo.vue';

export default {
	components: {
		pwBlockinfo
	}
}
</script>
