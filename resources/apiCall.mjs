//import apiConfig from './config.json';
const apiConfig = {
    "prod": {
        "url": "http://localhost/apina/public/api.php"
    },
    "test": {
        "url": "http://localhost/apina/public/api-test.php"
    }
};

/**
 * @typedef {{code: number, data: any}} ApinaResponseData
 */

/**
 * Calls Apina
 *
 * @param {bool} prod Production (true) or test (false) API connection
 * @param {string} method HTTP Request Method - GET, POST, ...
 * @param {string} url URL relative to the API endpoint, e.g. home '/', list '/type', resource '/type/id'
 * @param {*} body Data to send with the request (will be encoded as JSON)
 * @returns {ApinaResponseData} Response data
 */
export async function apiCall(prod, method, url, body = null) {
    let apiUrl = `${prod ? apiConfig.prod.url : apiConfig.test.url}${url}`;
    const apiRequest = {
        method
    };
    if (body !== null && method !== 'GET' && method !== 'HEAD') {
        apiRequest.body = JSON.stringify(body);
        apiRequest.headers = {
            'Content-Type': 'application/json'
        };
    }
    const res = await fetch(apiUrl, apiRequest)
        .then(async (res) => {
            let responseData = {
                code: res.status,
                data: [],
            };
            // if (responseData.url.startsWith(apiPrefix)) {
            //     responseData.url = responseData.url.slice(apiPrefix.length);
            // }
            //let json = [];
            try {
                responseData.data = await res.json();
                /*json = ((typeof rsp) === 'string') ? JSON.parse(rsp) : rsp;*/
            } catch (error) {
                responseData.data = [];
                console.warn('Incorrect JSON value', error);
            }
            return responseData;
        });
    return res;
}

/**
 * Register new resource type
 *
 * Input data: {string: {source: string, type: string, required?: bool, key?: string}}
 *
 * @param {string} name 
 * @param {any} data 
 * @returns {ApinaResponseData}
 */
export async function apiRegisterResource(name, data) {
    return apiCall('PUT', `/resource/${name}`, data);
}

/**
 * Get API base info
 * @returns {ApinaResponseData}
 */
export async function apiHome() {
    return apiCall('GET', `/`);
}

/**
 * Get resource data
 * @param {string} type Registered resource type
 * @param {string} id Resource ID
 * @returns {ApinaResponseData}
 */
export async function apiGetObject(type, id) {
    return apiCall('GET', `/${type}/${id}`);
}

/**
 * List all resources of the given type
 * @param {string} type Registered resource type
 * @returns {ApinaResponseData}
 */
export async function apiListObjects(type) {
    return apiCall('GET', `/${type}`);
}

/**
 * Check that a resource exists
 * @param {string} type Registered resource type
 * @param {string} id Resource ID
 * @returns {bool}
 */
export async function apiHasObject(type, id) {
    return apiCall('HEAD', `/${type}/${id}`);
}

/**
 * Check that a resource type exists
 * @param {string} type Registered resource type
 * @returns {bool}
 */
export async function apiHasObjects(type) {
    return apiCall('HEAD', `/${type}`);
}

/**
 * Create new or overwrite existing resource.
 * @param {string} type Registered resource type
 * @param {any} data Resource data (all required properties must be included)
 * @param {string|null} id Resource ID (will be generated automatically if id is null)
 * @returns {ApinaResponseData}
 */
export async function apiNewObject(type, data, id = null) {
    return apiCall('PUT', (id === null) ? `/${type}` : `/${type}/${id}`, data);
}

/**
 * Change existing object.
 * 
 * Note: if the data includes all required fields, it behaves the same as apiNewObject
 * @param {string} type Registered resource type
 * @param {any} data New resource data (required fields don't have to be included)
 * @param {string} id Resource ID
 * @returns {ApinaResponseData}
 */
export async function apiChangeObject(type, data, id) {
    return apiCall('POST', `/${type}/${id}`, data);
}

/**
 * Delete specific resource.
 * @param {string} type Registered resource type
 * @param {string} id Resource ID
 * @returns {ApinaResponseData}
 */
export async function apiDeleteObject(type, id) {
    return apiCall('DELETE', `/${type}/${id}`, data);
}

/**
 * Delete all resource with the given type.
 * 
 * NOT IMPLEMENTED
 * @param {string} type Registered resource type
 * @returns {ApinaResponseData}
 */
export async function apiDeleteObjects(type) {
    return apiCall('DELETE', `/${type}`, data);
}
