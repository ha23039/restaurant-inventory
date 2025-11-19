import { ref } from 'vue';
import axios from 'axios';

export function useApi(url, options = {}) {
    const data = ref(null);
    const error = ref(null);
    const loading = ref(false);

    const execute = async (config = {}) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios({
                url,
                ...options,
                ...config
            });

            data.value = response.data;
            return response.data;
        } catch (err) {
            error.value = err.response?.data || err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const get = (params = {}) => {
        return execute({ method: 'GET', params });
    };

    const post = (body = {}) => {
        return execute({ method: 'POST', data: body });
    };

    const put = (body = {}) => {
        return execute({ method: 'PUT', data: body });
    };

    const patch = (body = {}) => {
        return execute({ method: 'PATCH', data: body });
    };

    const del = () => {
        return execute({ method: 'DELETE' });
    };

    return {
        data,
        error,
        loading,
        execute,
        get,
        post,
        put,
        patch,
        delete: del
    };
}

export function useApiFetch(url, options = {}) {
    const api = useApi(url, options);

    // Auto-fetch on creation
    if (options.immediate !== false) {
        api.get();
    }

    return api;
}
