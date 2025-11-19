import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { ref, nextTick } from 'vue';
import { useDebounce, useDebounceFn } from '@/composables/useDebounce';

describe('useDebounce', () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('debounces value changes', async () => {
        const value = ref('initial');
        const debounced = useDebounce(value, 500);

        expect(debounced.value).toBe('initial');

        value.value = 'changed';
        await nextTick();
        expect(debounced.value).toBe('initial'); // Not changed yet

        vi.advanceTimersByTime(499);
        expect(debounced.value).toBe('initial'); // Still not changed

        vi.advanceTimersByTime(1);
        await nextTick();
        expect(debounced.value).toBe('changed'); // Now changed
    });

    it('resets timer on rapid changes', async () => {
        const value = ref('initial');
        const debounced = useDebounce(value, 500);

        value.value = 'change1';
        await nextTick();
        vi.advanceTimersByTime(300);

        value.value = 'change2';
        await nextTick();
        vi.advanceTimersByTime(300);

        expect(debounced.value).toBe('initial'); // Timer was reset

        vi.advanceTimersByTime(200);
        await nextTick();
        expect(debounced.value).toBe('change2'); // Final value after full delay
    });
});

describe('useDebounceFn', () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('debounces function calls', () => {
        const fn = vi.fn();
        const debouncedFn = useDebounceFn(fn, 500);

        debouncedFn('arg1');
        expect(fn).not.toHaveBeenCalled();

        vi.advanceTimersByTime(500);
        expect(fn).toHaveBeenCalledWith('arg1');
        expect(fn).toHaveBeenCalledTimes(1);
    });

    it('only calls function once for rapid calls', () => {
        const fn = vi.fn();
        const debouncedFn = useDebounceFn(fn, 500);

        debouncedFn('call1');
        debouncedFn('call2');
        debouncedFn('call3');

        vi.advanceTimersByTime(500);

        expect(fn).toHaveBeenCalledTimes(1);
        expect(fn).toHaveBeenCalledWith('call3');
    });
});
