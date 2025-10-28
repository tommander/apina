import * as api from '../../resources/apiCall.mjs';
import {ApinaHelper} from '../../resources/apinaHelper.mjs'
import testdefinition from '../common/test.json';

describe.each(testdefinition)('Direct API call', ({title, exp, act}) => {
    test(title, async () => {
        const rsp = await api.apiCall(false, act.method, act.url, act.data);
        expect(rsp).toEqual(exp);
    });
});

describe('Apina Helper', () => {
    var apihlp = new ApinaHelper(false);
    test('HLP-TEST-CLEAR', async () => {
        const res = await apihlp.deleteEverything();
        expect(res).toEqual({code: 200, data: []})
    });
    test('HLP-TEST-0', async () => {
        const res = await apihlp.registerResource('gallery', {
            "folder": {
                "source": "meta:folder",
                "type": "string",
                "required": true,
                "key": true
            },
            "name": {
                "source": "meta:name",
                "type": "string"
            },
            "photos": {
                "source": "meta:photos",
                "type": "array"
            },
            "addphoto": {
                "source": "add:photos",
                "type": "string"
            },
            "remphoto": {
                "source": "rem:photos",
                "type": "string"
            }
        });
        expect(res).toEqual({code: 200, data: {
            "folder": {
                "source": "meta:folder",
                "type": "string",
                "required": true,
                "key": true
            },
            "name": {
                "source": "meta:name",
                "type": "string"
            },
            "photos": {
                "source": "meta:photos",
                "type": "array"
            },
            "addphoto": {
                "source": "add:photos",
                "type": "string"
            },
            "remphoto": {
                "source": "rem:photos",
                "type": "string"
            },
            "_links": {
                "self": {
                    "href": "/resource/gallery",
                }
            }
        }})
    });
    test('HLP-TEST-2', async () => {
        const res = await apihlp.home();
        expect(res).toEqual({code: 200, data: {"_links": {"gallery": {"href": "/gallery"}}}})
    });
    test('HLP-TEST-3', async () => {
        const res = await apihlp.hasResourceType('nonexistent');
        expect(res).toEqual({code: 404, data: []})
    });
    test('HLP-TEST-4', async () => {
        const res = await apihlp.listResources('nonexistent');
        expect(res).toEqual({code: 404, data: {"error": {"message": "Unknown resource type \"nonexistent\""}}})
    });
    test('HLP-TEST-5', async () => {
        const res = await apihlp.hasResourceType('gallery');
        expect(res).toEqual({code: 200, data: []})
    });
    test('HLP-TEST-6', async () => {
        const res = await apihlp.listResources('gallery');
        expect(res).toEqual({code: 200, data: []})
    });
    test('HLP-TEST-7', async () => {
        const res = await apihlp.hasResource('gallery', 'non-existent');
        expect(res).toEqual({code: 404, data: []})
    });
    test('HLP-TEST-8', async () => {
        const res = await apihlp.getResource('gallery', 'non-existent');
        expect(res).toEqual({code: 404, data: {"error": {"message": "Resource \"/gallery/non-existent\" does not exist"}}})
    });
    test('HLP-TEST-9', async () => {
        const res = await apihlp.changeResource('gallery', {"hello": "world"}, '1');
        expect(res).toEqual({code: 400, data: {"error": {"message": "Invalid object data; missing required field \"folder\"."}}})
    });
    test('HLP-TEST-10', async () => {
        const res = await apihlp.getResource('gallery', '1');
        expect(res).toEqual({code: 404, data: {"error": {"message": "Resource \"/gallery/1\" does not exist"}}})
    });
    test('HLP-TEST-11', async () => {
        const res = await apihlp.createResource('gallery', {"hello": "world"}, '1');
        expect(res).toEqual({code: 400, data: {"error": {"message": "Invalid object data; missing required field \"folder\"."}}})
    });
    test('HLP-TEST-12', async () => {
        const res = await apihlp.getResource('gallery', '1');
        expect(res).toEqual({code: 404, data: {"error": {"message": "Resource \"/gallery/1\" does not exist"}}})
    });
    test('HLP-TEST-13', async () => {
        const res = await apihlp.createResource('gallery', {"folder": "dir1"}, '1');
        expect(res).toEqual({code: 200, data: {"folder": "dir1", "_links": {"self": {"href": "/gallery/1"}}}})
    });
    test('HLP-TEST-14', async () => {
        const res = await apihlp.getResource('gallery', '1');
        expect(res).toEqual({code: 200, data: {"folder": "dir1", "_links": {"self": {"href": "/gallery/1"}}}})
    });
    test('HLP-TEST-15', async () => {
        const res = await apihlp.listResources('gallery');
        expect(res).toEqual({code: 200, data: ["/gallery/1"]})
    });

    test('HLP-TEST-16', async () => {
        const res = await apihlp.changeResource('gallery', {"hello": "world"}, '1');
        expect(res).toEqual({code: 400, data: {"error": {"message": "Invalid object data; no known attribute included."}}})
    });
    test('HLP-TEST-17', async () => {
        const res = await apihlp.getResource('gallery', '1');
        expect(res).toEqual({code: 200, data: {"folder": "dir1", "_links": {"self": {"href": "/gallery/1"}}}})
    });
    test('HLP-TEST-18', async () => {
        const res = await apihlp.createResource('gallery', {"hello": "world"}, '1');
        expect(res).toEqual({code: 400, data: {"error": {"message": "Invalid object data; missing required field \"folder\"."}}})
    });
    test('HLP-TEST-19', async () => {
        const res = await apihlp.getResource('gallery', '1');
        expect(res).toEqual({code: 200, data: {"folder": "dir1", "_links": {"self": {"href": "/gallery/1"}}}})
    });
    test('HLP-TEST-20', async () => {
        const res = await apihlp.createResource('gallery', {"name": "foo"}, '1');
        expect(res).toEqual({code: 400, data: {"error": {"message": "Invalid object data; missing required field \"folder\"."}}})
    });
    test('HLP-TEST-21', async () => {
        const res = await apihlp.getResource('gallery', '1');
        expect(res).toEqual({code: 200, data: {"folder": "dir1", "_links": {"self": {"href": "/gallery/1"}}}})
    });
    test('HLP-TEST-22', async () => {
        const res = await apihlp.changeResource('gallery', {"name": "foo"}, '1');
        expect(res).toEqual({code: 200, data: {"folder": "dir1", "name": "foo", "_links": {"self": {"href": "/gallery/1"}}}})
    });
    test('HLP-TEST-23', async () => {
        const res = await apihlp.getResource('gallery', '1');
        expect(res).toEqual({code: 200, data: {"folder": "dir1", "name": "foo", "_links": {"self": {"href": "/gallery/1"}}}})
    });
    test('HLP-TEST-24', async () => {
        const res = await apihlp.changeResource('gallery', {"folder": "hello", "name": "foo"});
        expect(res).toEqual({code: 200, data: {"folder": "hello", "name": "foo", "_links": {"self": {"href": "/gallery/hello"}}}})
    });
    test('HLP-TEST-25', async () => {
        const res = await apihlp.getResource('gallery', 'hello');
        expect(res).toEqual({code: 200, data: {"folder": "hello", "name": "foo", "_links": {"self": {"href": "/gallery/hello"}}}})
    });
    test('HLP-TEST-26', async () => {
        const res = await apihlp.changeResource('gallery', {"folder": "hello", "name": "bar"});
        expect(res).toEqual({code: 200, data: {"folder": "hello", "name": "bar", "_links": {"self": {"href": "/gallery/hello2"}}}})
    });
    test('HLP-TEST-27', async () => {
        const res = await apihlp.getResource('gallery', 'hello2');
        expect(res).toEqual({code: 200, data: {"folder": "hello", "name": "bar", "_links": {"self": {"href": "/gallery/hello2"}}}})
    });
    test('HLP-TEST-28', async () => {
        const res = await apihlp.getResource('gallery', 'hello2');
        expect(res).toEqual({code: 200, data: {"folder": "hello", "name": "bar", "_links": {"self": {"href": "/gallery/hello2"}}}})
    });
    test('HLP-TEST-29', async () => {
        const res = await apihlp.listResources('gallery');
        expect(res).toEqual({code: 200, data: ["/gallery/1", "/gallery/hello", "/gallery/hello2"]})
    });
    test('HLP-TEST-30', async () => {
        const res = await apihlp.deleteResource('gallery', '1');
        expect(res).toEqual({code: 200, data: []})
    });
    test('HLP-TEST-31', async () => {
        const res = await apihlp.getResource('gallery', '1');
        expect(res).toEqual({code: 404, data: {"error": {"message": "Resource \"/gallery/1\" does not exist"}}})
    });
    test('HLP-TEST-32', async () => {
        const res = await apihlp.listResources('gallery');
        expect(res).toEqual({code: 200, data: ["/gallery/hello", "/gallery/hello2"]})
    });
    test('HLP-TEST-33', async () => {
        const res = await apihlp.changeResource('gallery', {"addphoto": "hello"}, 'hello');
        expect(res).toEqual({code: 200, data: {"folder": "hello", "name": "foo", "photos": ["hello"], "_links": {"self": {"href": "/gallery/hello"}}}})
    });
    test('HLP-TEST-34', async () => {
        const res = await apihlp.getResource('gallery', 'hello');
        expect(res).toEqual({code: 200, data: {"folder": "hello", "name": "foo", "photos": ["hello"], "_links": {"self": {"href": "/gallery/hello"}}}})
    });
    test('HLP-TEST-35', async () => {
        const res = await apihlp.changeResource('gallery', {"addphoto": "world"}, 'hello');
        expect(res).toEqual({code: 200, data: {"folder": "hello", "name": "foo", "photos": ["hello", "world"], "_links": {"self": {"href": "/gallery/hello"}}}})
    });
    test('HLP-TEST-36', async () => {
        const res = await apihlp.getResource('gallery', 'hello');
        expect(res).toEqual({code: 200, data: {"folder": "hello", "name": "foo", "photos": ["hello", "world"], "_links": {"self": {"href": "/gallery/hello"}}}})
    });
    test('HLP-TEST-37', async () => {
        const res = await apihlp.changeResource('gallery', {"remphoto": "hello"}, 'hello');
        expect(res).toEqual({code: 200, data: {"folder": "hello", "name": "foo", "photos": ["world"], "_links": {"self": {"href": "/gallery/hello"}}}})
    });
    test('HLP-TEST-38', async () => {
        const res = await apihlp.getResource('gallery', 'hello');
        expect(res).toEqual({code: 200, data: {"folder": "hello", "name": "foo", "photos": ["world"], "_links": {"self": {"href": "/gallery/hello"}}}})
    });

    test('HLP-TEST-39', async () => {
        const res = await apihlp.deleteResourceType('gallery');
        expect(res).toEqual({code: 200, data: ["/gallery/hello", "/gallery/hello2", "/resource/gallery"]})
    });
    test('HLP-TEST-40', async () => {
        const res = await apihlp.listResources('gallery');
        expect(res).toEqual({code: 404, data: {"error": {"message": "Unknown resource type \"gallery\""}}})
    });
    test('HLP-TEST-41', async () => {
        const res = await apihlp.getResource('gallery', 'hello');
        expect(res).toEqual({code: 404, data: {"error": {"message": "Unknown resource type \"gallery\""}}})
    });
});