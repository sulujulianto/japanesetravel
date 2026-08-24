import pluginVue from 'eslint-plugin-vue';
import { vueTsConfigs, withVueTs } from '@vue/eslint-config-typescript';

export default withVueTs(
    {
        rootDir: import.meta.dirname,
    },
    {
        ignores: ['node_modules/**', 'public/build/**'],
    },
    pluginVue.configs['flat/essential'],
    vueTsConfigs.recommended,
    {
        files: ['resources/js/**/*.{js,ts,vue}'],
        rules: {
            'no-duplicate-imports': 'error',
        },
    },
);
