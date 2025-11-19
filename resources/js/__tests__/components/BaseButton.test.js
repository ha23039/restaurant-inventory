import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BaseButton from '@/Components/Base/BaseButton.vue';

describe('BaseButton', () => {
    it('renders properly with default props', () => {
        const wrapper = mount(BaseButton, {
            slots: {
                default: 'Click me'
            }
        });

        expect(wrapper.text()).toContain('Click me');
        expect(wrapper.find('button').exists()).toBe(true);
    });

    it('emits click event when clicked', async () => {
        const wrapper = mount(BaseButton);
        await wrapper.trigger('click');
        expect(wrapper.emitted()).toHaveProperty('click');
    });

    it('does not emit click when disabled', async () => {
        const wrapper = mount(BaseButton, {
            props: { disabled: true }
        });

        await wrapper.trigger('click');
        expect(wrapper.emitted('click')).toBeFalsy();
    });

    it('does not emit click when loading', async () => {
        const wrapper = mount(BaseButton, {
            props: { loading: true }
        });

        await wrapper.trigger('click');
        expect(wrapper.emitted('click')).toBeFalsy();
    });

    it('shows loading spinner when loading', () => {
        const wrapper = mount(BaseButton, {
            props: { loading: true }
        });

        expect(wrapper.find('svg').exists()).toBe(true);
        expect(wrapper.find('.animate-spin').exists()).toBe(true);
    });

    it('applies correct variant classes', () => {
        const wrapper = mount(BaseButton, {
            props: { variant: 'danger' }
        });

        const button = wrapper.find('button');
        expect(button.classes()).toContain('bg-red-600');
    });

    it('applies correct size classes', () => {
        const wrapper = mount(BaseButton, {
            props: { size: 'lg' }
        });

        const button = wrapper.find('button');
        expect(button.classes()).toContain('px-6');
        expect(button.classes()).toContain('py-3');
    });

    it('renders outline variant', () => {
        const wrapper = mount(BaseButton, {
            props: { variant: 'primary', outline: true }
        });

        const button = wrapper.find('button');
        expect(button.classes()).toContain('border-blue-600');
    });

    it('has correct button type', () => {
        const wrapper = mount(BaseButton, {
            props: { type: 'submit' }
        });

        expect(wrapper.find('button').attributes('type')).toBe('submit');
    });
});
