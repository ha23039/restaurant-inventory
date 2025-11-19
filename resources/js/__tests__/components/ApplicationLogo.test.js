import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

describe('ApplicationLogo', () => {
    it('renders properly', () => {
        const wrapper = mount(ApplicationLogo);
        expect(wrapper.exists()).toBe(true);
    });

    it('renders an SVG element', () => {
        const wrapper = mount(ApplicationLogo);
        expect(wrapper.find('svg').exists()).toBe(true);
    });

    it('has correct viewBox attribute', () => {
        const wrapper = mount(ApplicationLogo);
        const svg = wrapper.find('svg');
        expect(svg.attributes('viewBox')).toBe('0 0 316 316');
    });
});
