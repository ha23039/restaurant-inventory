import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import SearchBar from '@/Components/Data/SearchBar.vue';

describe('SearchBar', () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('renders properly with default props', () => {
        const wrapper = mount(SearchBar);

        expect(wrapper.find('input').exists()).toBe(true);
        expect(wrapper.find('input').attributes('placeholder')).toBe('Buscar...');
    });

    it('shows search icon by default', () => {
        const wrapper = mount(SearchBar);

        const searchIcon = wrapper.find('svg');
        expect(searchIcon.exists()).toBe(true);
    });

    it('shows loading spinner when loading', () => {
        const wrapper = mount(SearchBar, {
            props: { loading: true },
        });

        const spinner = wrapper.find('.animate-spin');
        expect(spinner.exists()).toBe(true);
    });

    it('emits debounced search event', async () => {
        const wrapper = mount(SearchBar, {
            props: { debounce: 300 },
        });

        const input = wrapper.find('input');
        await input.setValue('test query');

        // Avanzar el tiempo para activar el debounce
        vi.advanceTimersByTime(300);
        await wrapper.vm.$nextTick();

        expect(wrapper.emitted('search')).toBeTruthy();
        expect(wrapper.emitted('search')[0]).toEqual(['test query']);
    });

    it('shows clear button when input has value', async () => {
        const wrapper = mount(SearchBar, {
            props: { modelValue: 'test' },
        });

        await wrapper.vm.$nextTick();

        const clearButton = wrapper.find('button');
        expect(clearButton.exists()).toBe(true);
    });

    it('clears input when clear button is clicked', async () => {
        const wrapper = mount(SearchBar, {
            props: { modelValue: 'test' },
        });

        await wrapper.vm.$nextTick();

        const clearButton = wrapper.find('button');
        await clearButton.trigger('click');

        expect(wrapper.emitted('update:modelValue')).toBeTruthy();
        expect(wrapper.emitted('update:modelValue')[0]).toEqual(['']);
        expect(wrapper.emitted('clear')).toBeTruthy();
    });

    it('does not show clear button when showClear is false', async () => {
        const wrapper = mount(SearchBar, {
            props: {
                modelValue: 'test',
                showClear: false,
            },
        });

        await wrapper.vm.$nextTick();

        const clearButton = wrapper.find('button');
        expect(clearButton.exists()).toBe(false);
    });

    it('disables input when disabled prop is true', () => {
        const wrapper = mount(SearchBar, {
            props: { disabled: true },
        });

        const input = wrapper.find('input');
        expect(input.attributes('disabled')).toBeDefined();
    });

    it('uses custom placeholder', () => {
        const placeholder = 'Buscar productos...';
        const wrapper = mount(SearchBar, {
            props: { placeholder },
        });

        expect(wrapper.find('input').attributes('placeholder')).toBe(placeholder);
    });
});
