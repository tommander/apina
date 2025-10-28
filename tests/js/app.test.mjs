import * as api from '../../resources/apiCall.mjs';
import {ApinaHelper} from '../../resources/apinaHelper.mjs'
import testdefinition from '../common/test.json';
import testdefinition2 from '../common/test2.json';

describe.each(testdefinition)('Direct API call', ({title, exp, act}) => {
    test(title, async () => {
        const rsp = await api.apiCall(false, act.method, act.url, act.data);
        expect(rsp).toEqual(exp);
    });
});

const apihlp = new ApinaHelper(false);

describe.each(testdefinition2)('Apina Helper', ({title, exp, act}) => {
    test(title, async () => {
        const rsp = await apihlp[act.method](...act.params);
        expect(rsp).toEqual(exp);
    });
});
