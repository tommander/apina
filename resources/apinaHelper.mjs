/**
 * @typedef {{code: number, data: any}} ApinaResponseData
 */
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
 * ApinaHelper
 *
 * @param {bool} prod Production (true) or test (false) API connection
 */
export var ApinaHelper = function (prod) {
    this.prod = prod;

    /**
     * Calls Apina
     *
     * @param {string} method HTTP Request Method - GET, POST, ...
     * @param {string} url URL relative to the API endpoint, e.g. home '/', list '/type', resource '/type/id'
     * @param {*} body Data to send with the request (will be encoded as JSON)
     * @returns {ApinaResponseData} Response data
     */
    this.doCall = async (method, url, body = null) => {
        let apiUrl = `${this.prod ? apiConfig.prod.url : apiConfig.test.url}${url}`;
        // console.debug('ApinaHelper: URL is: ', apiUrl);
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
                try {
                    responseData.data = await res.json();
                } catch (error) {
                    responseData.data = [];
                    console.debug('ApinaHelper: response is not a JSON', error);
                }
                return responseData;
            });
        return res;
    };

    /**
     * Register new resource type
     *
     * Input data: {string: {source: string, type: string, required?: bool, key?: string}}
     *
     * @param {string} name 
     * @param {any} data 
     * @returns {ApinaResponseData}
     */
    this.registerResource = async (name, data) => {
        return this.doCall('PUT', `/resource/${name}`, data);
    }

    /**
     * Get API base info
     * @returns {ApinaResponseData}
     */
    this.home = async () => {
        return this.doCall('GET', `/`);
    }

    /**
     * Get resource data
     * @param {string} type Registered resource type
     * @param {string} id Resource ID
     * @returns {ApinaResponseData}
     */
    this.getResource = async (type, id) => {
        return this.doCall('GET', `/${type}/${id}`);
    }

    /**
     * List all resources of the given type
     * @param {string} type Registered resource type
     * @returns {ApinaResponseData}
     */
    this.listResources = async (type) => {
        return this.doCall('GET', `/${type}`);
    }

    /**
     * Check that a resource exists
     * @param {string} type Registered resource type
     * @param {string} id Resource ID
     * @returns {bool}
     */
    this.hasResource = async (type, id) => {
        return this.doCall('HEAD', `/${type}/${id}`);
    }

    /**
     * Check that a resource type exists
     * @param {string} type Registered resource type
     * @returns {bool}
     */
    this.hasResourceType = async (type) => {
        return this.doCall('HEAD', `/${type}`);
    }

    /**
     * Create new or overwrite existing resource.
     * @param {string} type Registered resource type
     * @param {any} data Resource data (all required properties must be included)
     * @param {string|null} id Resource ID (will be generated automatically if id is null)
     * @returns {ApinaResponseData}
     */
    this.createResource = async (type, data, id = null) => {
        return this.doCall('PUT', (id === null) ? `/${type}` : `/${type}/${id}`, data);
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
    this.changeResource = async (type, data, id = null) => {
        return this.doCall('POST', (id === null) ? `/${type}` : `/${type}/${id}`, data);
    }

    /**
     * Delete specific resource.
     * @param {string} type Registered resource type
     * @param {string} id Resource ID
     * @returns {ApinaResponseData}
     */
    this.deleteResource = async (type, id) => {
        return this.doCall('DELETE', `/${type}/${id}`);
    }

    /**
     * Delete all resources with the given type.
     * 
     * @param {string} type Registered resource type
     * @returns {ApinaResponseData}
     */
    this.deleteResourceType = async (type) => {
        return this.doCall('DELETE', `/${type}`);
    }

    /**
     * Delete everything.
     * 
     * @returns {ApinaResponseData}
     */
    this.deleteEverything = async () => {
        return this.doCall('DELETE', '/');
    }
}
