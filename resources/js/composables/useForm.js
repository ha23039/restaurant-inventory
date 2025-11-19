import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useForm(initialData = {}) {
    const data = reactive({ ...initialData });
    const errors = ref({});
    const processing = ref(false);
    const recentlySuccessful = ref(false);
    const wasSuccessful = ref(false);

    const reset = (...fields) => {
        if (fields.length === 0) {
            Object.keys(data).forEach(key => {
                data[key] = initialData[key];
            });
        } else {
            fields.forEach(field => {
                data[field] = initialData[field];
            });
        }
        errors.value = {};
    };

    const clearErrors = (...fields) => {
        if (fields.length === 0) {
            errors.value = {};
        } else {
            fields.forEach(field => {
                delete errors.value[field];
            });
        }
    };

    const setError = (field, message) => {
        errors.value[field] = message;
    };

    const submit = (method, url, options = {}) => {
        processing.value = true;
        errors.value = {};
        wasSuccessful.value = false;

        router[method](url, data, {
            ...options,
            onError: (responseErrors) => {
                errors.value = responseErrors;
                processing.value = false;
                if (options.onError) {
                    options.onError(responseErrors);
                }
            },
            onSuccess: (page) => {
                processing.value = false;
                recentlySuccessful.value = true;
                wasSuccessful.value = true;

                setTimeout(() => {
                    recentlySuccessful.value = false;
                }, 2000);

                if (options.onSuccess) {
                    options.onSuccess(page);
                }
            },
            onFinish: () => {
                processing.value = false;
                if (options.onFinish) {
                    options.onFinish();
                }
            }
        });
    };

    const post = (url, options) => submit('post', url, options);
    const put = (url, options) => submit('put', url, options);
    const patch = (url, options) => submit('patch', url, options);
    const del = (url, options) => submit('delete', url, options);

    return {
        data,
        errors,
        processing,
        recentlySuccessful,
        wasSuccessful,
        reset,
        clearErrors,
        setError,
        post,
        put,
        patch,
        delete: del,
    };
}
