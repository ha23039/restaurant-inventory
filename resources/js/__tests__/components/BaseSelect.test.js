import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BaseSelect from '@/Components/Base/BaseSelect.vue';

describe('BaseSelect', () => {
    const options = [
        { value: '1', label: 'Opción 1' },
        { value: '2', label: 'Opción 2' },
        { value: '3', label: 'Opción 3' },
    ];

    it('renders properly with options', () => {
        const wrapper = mount(BaseSelect, {
            props: { options },
        });

        expect(wrapper.find('select').exists()).toBe(true);
        expect(wrapper.findAll('option')).toHaveLength(options.length + 1); // +1 for placeholder
    });

    it('displays placeholder option', () => {
        const placeholder = 'Selecciona una opción';
        const wrapper = mount(BaseSelect, {
            props: {
                options,
                placeholder,
            },
        });

        const placeholderOption = wrapper.find('option[value=""]');
        expect(placeholderOption.exists()).toBe(true);
        expect(placeholderOption.text()).toBe(placeholder);
    });

    it('emits update:modelValue when selection changes', async () => {
        const wrapper = mount(BaseSelect, {
            props: {
                options,
                modelValue: '1',
            },
        });

        await wrapper.find('select').setValue('2');
        expect(wrapper.emitted('update:modelValue')).toBeTruthy();
        expect(wrapper.emitted('update:modelValue')[0]).toEqual(['2']);
    });

    it('applies error styles when error prop is set', () => {
        const wrapper = mount(BaseSelect, {
            props: {
                options,
                error: 'Este campo es requerido',
            },
        });

        const select = wrapper.find('select');
        expect(select.classes()).toContain('border-red-300');
        expect(wrapper.text()).toContain('Este campo es requerido');
    });

    it('disables select when disabled prop is true', () => {
        const wrapper = mount(BaseSelect, {
            props: {
                options,
                disabled: true,
            },
        });

        expect(wrapper.find('select').attributes('disabled')).toBeDefined();
    });

    it('supports multiple selection', async () => {
        const wrapper = mount(BaseSelect, {
            props: {
                options,
                multiple: true,
                modelValue: [],
            },
        });

        const select = wrapper.find('select');
        expect(select.attributes('multiple')).toBeDefined();
    });

    it('applies correct size classes', () => {
        const wrapper = mount(BaseSelect, {
            props: {
                options,
                size: 'lg',
            },
        });

        const select = wrapper.find('select');
        expect(select.classes()).toContain('py-3');
        expect(select.classes()).toContain('px-4');
    });
});
