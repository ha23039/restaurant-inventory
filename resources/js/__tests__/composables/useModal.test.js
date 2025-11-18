import { describe, it, expect, vi } from 'vitest';
import { useModal } from '@/composables/useModal';

describe('useModal', () => {
    it('initializes with closed state', () => {
        const modal = useModal();
        expect(modal.isOpen.value).toBe(false);
        expect(modal.data.value).toBeNull();
    });

    it('opens modal', () => {
        const modal = useModal();
        modal.open();
        expect(modal.isOpen.value).toBe(true);
    });

    it('opens modal with data', () => {
        const modal = useModal();
        const testData = { id: 1, name: 'Test' };

        modal.open(testData);

        expect(modal.isOpen.value).toBe(true);
        expect(modal.data.value).toEqual(testData);
    });

    it('closes modal', () => {
        vi.useFakeTimers();

        const modal = useModal();
        modal.open({ test: 'data' });

        expect(modal.isOpen.value).toBe(true);

        modal.close();

        expect(modal.isOpen.value).toBe(false);

        // Data should be cleared after animation
        vi.advanceTimersByTime(300);
        expect(modal.data.value).toBeNull();

        vi.restoreAllMocks();
    });

    it('toggles modal state', () => {
        const modal = useModal();

        expect(modal.isOpen.value).toBe(false);

        modal.toggle();
        expect(modal.isOpen.value).toBe(true);

        modal.toggle();
        expect(modal.isOpen.value).toBe(false);
    });
});
