<template>
	<!-- a question in the drawer: the question and its answer (only what is
	     filled in: no placeholders) -->
	<div class="pwPreview" @dblclick="open">
		<div class="item">
			<div v-if="content.question" class="pwHeading">{{ content.question }}</div>
			<div v-if="answer" class="pwText">
				<div v-if="answerIsHtml" v-html="answer"></div>
				<div v-else class="pwPlain">{{ answer }}</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	computed: {
		answerData() {
			try {
				return JSON.parse(this.content.answer || '{}') || {};
			} catch (e) {
				return {};
			}
		},
		// the writer's HTML; plain text as it is (masked)
		answerIsHtml() {
			return (this.answerData.mode || 'textarea') === 'writer';
		},
		answer() {
			const d = this.answerData;
			return d[d.mode || 'textarea'] || '';
		}
	}
}
</script>

<style scoped>
div.item {
	display: flex;
	flex-direction: column;
	gap: var(--spacing-1);
	padding: var(--spacing-2);
	font-size: var(--text-sm);

	/* (a step larger than the text: stands out from bold in it) */
	div.pwHeading {
		font-size: var(--text-md);
		font-weight: var(--font-bold);
		padding: var(--spacing-1) 0;
	}
	div.pwPlain {
		white-space: pre-line;
	}
	div.pwText {
		line-height: 1.2rem;
		opacity: 0.8;
		word-break: break-word;
		overflow-wrap: anywhere;
	}
}
</style>
