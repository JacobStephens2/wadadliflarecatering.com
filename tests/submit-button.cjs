const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const context = {
    document: {
        addEventListener() {}, querySelector: () => null, querySelectorAll: () => [],
        createElement: () => ({}), head: { appendChild() {} },
    },
    window: { addEventListener() {} },
};
vm.createContext(context);
vm.runInContext(fs.readFileSync('public/js/main.js', 'utf8'), context);
const button = { disabled: false };
const field = { value: 'Lynch', classList: { add() {}, remove() {} } };
const form = {
    dataset: {},
    querySelectorAll: selector => selector === '[required]' ? [field] : selector === 'input[type="email"]' ? [] : [button],
};
field.value = '';
assert.equal(context.validateForm(form), false, 'Invalid input stays editable');
assert.equal(button.disabled, false);
field.value = 'Lynch';
assert.equal(context.validateForm(form), true, 'First valid submit is allowed');
assert.equal(button.disabled, true, 'Submit button is disabled');
assert.equal(context.validateForm(form), false, 'Second submit is suppressed');
console.log('PASS submit button');
