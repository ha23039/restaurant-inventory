import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import DataTable from '@/Components/Data/DataTable.vue';

describe('DataTable', () => {
    const columns = [
        { key: 'id', label: 'ID' },
        { key: 'name', label: 'Nombre' },
        { key: 'price', label: 'Precio', align: 'right' },
    ];

    const data = [
        { id: 1, name: 'Producto 1', price: 100 },
        { id: 2, name: 'Producto 2', price: 200 },
        { id: 3, name: 'Producto 3', price: 150 },
    ];

    it('renders table with columns and data', () => {
        const wrapper = mount(DataTable, {
            props: { columns, data },
        });

        expect(wrapper.find('table').exists()).toBe(true);
        expect(wrapper.findAll('thead th')).toHaveLength(columns.length);
        expect(wrapper.findAll('tbody tr')).toHaveLength(data.length);
    });

    it('displays column headers correctly', () => {
        const wrapper = mount(DataTable, {
            props: { columns, data },
        });

        const headers = wrapper.findAll('thead th');
        expect(headers[0].text()).toContain('ID');
        expect(headers[1].text()).toContain('Nombre');
        expect(headers[2].text()).toContain('Precio');
    });

    it('displays empty state when no data', () => {
        const wrapper = mount(DataTable, {
            props: { columns, data: [] },
        });

        expect(wrapper.text()).toContain('No hay datos disponibles');
    });

    it('shows loading spinner when loading', () => {
        const wrapper = mount(DataTable, {
            props: { columns, data: [], loading: true },
        });

        expect(wrapper.find('.animate-spin').exists()).toBe(true);
    });

    it('emits row-click event when row is clicked', async () => {
        const wrapper = mount(DataTable, {
            props: { columns, data },
        });

        const firstRow = wrapper.findAll('tbody tr')[0];
        await firstRow.trigger('click');

        expect(wrapper.emitted('row-click')).toBeTruthy();
        expect(wrapper.emitted('row-click')[0][0].row).toEqual(data[0]);
    });

    it('sorts data when column header is clicked', async () => {
        const wrapper = mount(DataTable, {
            props: { columns, data, sortable: true },
        });

        // Click on price column to sort
        const priceHeader = wrapper.findAll('thead th')[2];
        await priceHeader.trigger('click');

        expect(wrapper.emitted('sort')).toBeTruthy();
        expect(wrapper.emitted('sort')[0][0]).toEqual({ key: 'price', order: 'asc' });
    });

    it('applies hover effect when hoverable is true', () => {
        const wrapper = mount(DataTable, {
            props: { columns, data, hoverable: true },
        });

        const firstRow = wrapper.findAll('tbody tr')[0];
        expect(firstRow.classes()).toContain('hover:bg-gray-50');
    });

    it('applies striped rows when striped is true', () => {
        const wrapper = mount(DataTable, {
            props: { columns, data, striped: true },
        });

        const rows = wrapper.findAll('tbody tr');
        expect(rows[1].classes()).toContain('bg-gray-50');
    });

    it('uses custom empty message', () => {
        const emptyMessage = 'No se encontraron resultados';
        const wrapper = mount(DataTable, {
            props: { columns, data: [], emptyMessage },
        });

        expect(wrapper.text()).toContain(emptyMessage);
    });

    it('handles nested data keys', () => {
        const nestedColumns = [
            { key: 'user.name', label: 'Usuario' },
            { key: 'user.email', label: 'Email' },
        ];

        const nestedData = [
            { id: 1, user: { name: 'Juan', email: 'juan@example.com' } },
        ];

        const wrapper = mount(DataTable, {
            props: { columns: nestedColumns, data: nestedData },
        });

        expect(wrapper.text()).toContain('Juan');
        expect(wrapper.text()).toContain('juan@example.com');
    });
});
