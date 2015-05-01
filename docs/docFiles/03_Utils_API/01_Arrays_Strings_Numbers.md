The *Control Freak* SDK provides a general purpose API dealing with arrays, numbers and strings. This API is implemented
by <a href="https://lodash.com/">lodash</a> <span>Lodash</span>

<!-- div class="toc-container" -->

<!-- div -->

## `Array`
* <a href="#_chunkarray-size1">`_.chunk`</a>
* <a href="#_compactarray">`_.compact`</a>
* <a href="#_differencearray-values">`_.difference`</a>
* <a href="#_droparray-n1">`_.drop`</a>
* <a href="#_droprightarray-n1">`_.dropRight`</a>
* <a href="#_droprightwhilearray-predicate_identity-thisarg">`_.dropRightWhile`</a>
* <a href="#_dropwhilearray-predicate_identity-thisarg">`_.dropWhile`</a>
* <a href="#_fillarray-value-start0-endarraylength">`_.fill`</a>
* <a href="#_findindexarray-predicate_identity-thisarg">`_.findIndex`</a>
* <a href="#_findlastindexarray-predicate_identity-thisarg">`_.findLastIndex`</a>
* <a href="#_firstarray">`_.first`</a>
* <a href="#_flattenarray-isdeep">`_.flatten`</a>
* <a href="#_flattendeeparray">`_.flattenDeep`</a>
* <a href="#_firstarray" class="alias">`_.head` -> `first`</a>
* <a href="#_indexofarray-value-fromindex0">`_.indexOf`</a>
* <a href="#_initialarray">`_.initial`</a>
* <a href="#_intersectionarrays">`_.intersection`</a>
* <a href="#_lastarray">`_.last`</a>
* <a href="#_lastindexofarray-value-fromindexarraylength-1">`_.lastIndexOf`</a>
* <a href="#_zipobjectprops-values" class="alias">`_.object` -> `zipObject`</a>
* <a href="#_pullarray-values">`_.pull`</a>
* <a href="#_pullatarray-indexes">`_.pullAt`</a>
* <a href="#_removearray-predicate_identity-thisarg">`_.remove`</a>
* <a href="#_restarray">`_.rest`</a>
* <a href="#_slicearray-start0-endarraylength">`_.slice`</a>
* <a href="#_sortedindexarray-value-iteratee_identity-thisarg">`_.sortedIndex`</a>
* <a href="#_sortedlastindexarray-value-iteratee_identity-thisarg">`_.sortedLastIndex`</a>
* <a href="#_restarray" class="alias">`_.tail` -> `rest`</a>
* <a href="#_takearray-n1">`_.take`</a>
* <a href="#_takerightarray-n1">`_.takeRight`</a>
* <a href="#_takerightwhilearray-predicate_identity-thisarg">`_.takeRightWhile`</a>
* <a href="#_takewhilearray-predicate_identity-thisarg">`_.takeWhile`</a>
* <a href="#_unionarrays">`_.union`</a>
* <a href="#_uniqarray-issorted-iteratee-thisarg">`_.uniq`</a>
* <a href="#_uniqarray-issorted-iteratee-thisarg" class="alias">`_.unique` -> `uniq`</a>
* <a href="#_unziparray">`_.unzip`</a>
* <a href="#_withoutarray-values">`_.without`</a>
* <a href="#_xorarrays">`_.xor`</a>
* <a href="#_ziparrays">`_.zip`</a>
* <a href="#_zipobjectprops-values">`_.zipObject`</a>

<!-- /div -->

<!-- div -->

## `Chain`
* <a href="#_value">`_`</a>
* <a href="#_chainvalue">`_.chain`</a>
* <a href="#_tapvalue-interceptor-thisarg">`_.tap`</a>
* <a href="#_thruvalue-interceptor-thisarg">`_.thru`</a>
* <a href="#_prototypechain">`_.prototype.chain`</a>
* <a href="#_prototypecommit">`_.prototype.commit`</a>
* <a href="#_prototypeplant">`_.prototype.plant`</a>
* <a href="#_prototypereverse">`_.prototype.reverse`</a>
* <a href="#_prototypevalue" class="alias">`_.prototype.run` -> `value`</a>
* <a href="#_prototypevalue" class="alias">`_.prototype.toJSON` -> `value`</a>
* <a href="#_prototypetostring">`_.prototype.toString`</a>
* <a href="#_prototypevalue">`_.prototype.value`</a>
* <a href="#_prototypevalue" class="alias">`_.prototype.valueOf` -> `value`</a>

<!-- /div -->

<!-- div -->

## `Collection`
* <a href="#_everycollection-predicate_identity-thisarg" class="alias">`_.all` -> `every`</a>
* <a href="#_somecollection-predicate_identity-thisarg" class="alias">`_.any` -> `some`</a>
* <a href="#_atcollection-props">`_.at`</a>
* <a href="#_mapcollection-iteratee_identity-thisarg" class="alias">`_.collect` -> `map`</a>
* <a href="#_includescollection-target-fromindex0" class="alias">`_.contains` -> `includes`</a>
* <a href="#_countbycollection-iteratee_identity-thisarg">`_.countBy`</a>
* <a href="#_findcollection-predicate_identity-thisarg" class="alias">`_.detect` -> `find`</a>
* <a href="#_foreachcollection-iteratee_identity-thisarg" class="alias">`_.each` -> `forEach`</a>
* <a href="#_foreachrightcollection-iteratee_identity-thisarg" class="alias">`_.eachRight` -> `forEachRight`</a>
* <a href="#_everycollection-predicate_identity-thisarg">`_.every`</a>
* <a href="#_filtercollection-predicate_identity-thisarg">`_.filter`</a>
* <a href="#_findcollection-predicate_identity-thisarg">`_.find`</a>
* <a href="#_findlastcollection-predicate_identity-thisarg">`_.findLast`</a>
* <a href="#_findwherecollection-source">`_.findWhere`</a>
* <a href="#_reducecollection-iteratee_identity-accumulator-thisarg" class="alias">`_.foldl` -> `reduce`</a>
* <a href="#_reducerightcollection-iteratee_identity-accumulator-thisarg" class="alias">`_.foldr` -> `reduceRight`</a>
* <a href="#_foreachcollection-iteratee_identity-thisarg">`_.forEach`</a>
* <a href="#_foreachrightcollection-iteratee_identity-thisarg">`_.forEachRight`</a>
* <a href="#_groupbycollection-iteratee_identity-thisarg">`_.groupBy`</a>
* <a href="#_includescollection-target-fromindex0" class="alias">`_.include` -> `includes`</a>
* <a href="#_includescollection-target-fromindex0">`_.includes`</a>
* <a href="#_indexbycollection-iteratee_identity-thisarg">`_.indexBy`</a>
* <a href="#_reducecollection-iteratee_identity-accumulator-thisarg" class="alias">`_.inject` -> `reduce`</a>
* <a href="#_invokecollection-methodname-args">`_.invoke`</a>
* <a href="#_mapcollection-iteratee_identity-thisarg">`_.map`</a>
* <a href="#_partitioncollection-predicate_identity-thisarg">`_.partition`</a>
* <a href="#_pluckcollection-key">`_.pluck`</a>
* <a href="#_reducecollection-iteratee_identity-accumulator-thisarg">`_.reduce`</a>
* <a href="#_reducerightcollection-iteratee_identity-accumulator-thisarg">`_.reduceRight`</a>
* <a href="#_rejectcollection-predicate_identity-thisarg">`_.reject`</a>
* <a href="#_samplecollection-n">`_.sample`</a>
* <a href="#_filtercollection-predicate_identity-thisarg" class="alias">`_.select` -> `filter`</a>
* <a href="#_shufflecollection">`_.shuffle`</a>
* <a href="#_sizecollection">`_.size`</a>
* <a href="#_somecollection-predicate_identity-thisarg">`_.some`</a>
* <a href="#_sortbycollection-iteratee_identity-thisarg">`_.sortBy`</a>
* <a href="#_sortbyallcollection-props">`_.sortByAll`</a>
* <a href="#_sortbyordercollection-props-orders">`_.sortByOrder`</a>
* <a href="#_wherecollection-source">`_.where`</a>

<!-- /div -->

<!-- div -->

## `Date`
* <a href="#_now">`_.now`</a>

<!-- /div -->

<!-- div -->

## `Function`
* <a href="#_aftern-func">`_.after`</a>
* <a href="#_aryfunc-nfunclength">`_.ary`</a>
* <a href="#_flowrightfuncs" class="alias">`_.backflow` -> `flowRight`</a>
* <a href="#_beforen-func">`_.before`</a>
* <a href="#_bindfunc-thisarg-partials">`_.bind`</a>
* <a href="#_bindallobject-methodnames">`_.bindAll`</a>
* <a href="#_bindkeyobject-key-partials">`_.bindKey`</a>
* <a href="#_flowrightfuncs" class="alias">`_.compose` -> `flowRight`</a>
* <a href="#_curryfunc-arityfunclength">`_.curry`</a>
* <a href="#_curryrightfunc-arityfunclength">`_.curryRight`</a>
* <a href="#_debouncefunc-wait0-options">`_.debounce`</a>
* <a href="#_deferfunc-args">`_.defer`</a>
* <a href="#_delayfunc-wait-args">`_.delay`</a>
* <a href="#_flowfuncs">`_.flow`</a>
* <a href="#_flowrightfuncs">`_.flowRight`</a>
* <a href="#_memoizefunc-resolver">`_.memoize`</a>
* <a href="#_negatepredicate">`_.negate`</a>
* <a href="#_oncefunc">`_.once`</a>
* <a href="#_partialfunc-partials">`_.partial`</a>
* <a href="#_partialrightfunc-partials">`_.partialRight`</a>
* <a href="#_reargfunc-indexes">`_.rearg`</a>
* <a href="#_restparamfunc-startfunclength-1">`_.restParam`</a>
* <a href="#_spreadfunc">`_.spread`</a>
* <a href="#_throttlefunc-wait0-options">`_.throttle`</a>
* <a href="#_wrapvalue-wrapper">`_.wrap`</a>

<!-- /div -->

<!-- div -->

## `Lang`
* <a href="#_clonevalue-isdeep-customizer-thisarg">`_.clone`</a>
* <a href="#_clonedeepvalue-customizer-thisarg">`_.cloneDeep`</a>
* <a href="#_isargumentsvalue">`_.isArguments`</a>
* <a href="#_isarrayvalue">`_.isArray`</a>
* <a href="#_isbooleanvalue">`_.isBoolean`</a>
* <a href="#_isdatevalue">`_.isDate`</a>
* <a href="#_iselementvalue">`_.isElement`</a>
* <a href="#_isemptyvalue">`_.isEmpty`</a>
* <a href="#_isequalvalue-other-customizer-thisarg">`_.isEqual`</a>
* <a href="#_iserrorvalue">`_.isError`</a>
* <a href="#_isfinitevalue">`_.isFinite`</a>
* <a href="#_isfunctionvalue">`_.isFunction`</a>
* <a href="#_ismatchobject-source-customizer-thisarg">`_.isMatch`</a>
* <a href="#_isnanvalue">`_.isNaN`</a>
* <a href="#_isnativevalue">`_.isNative`</a>
* <a href="#_isnullvalue">`_.isNull`</a>
* <a href="#_isnumbervalue">`_.isNumber`</a>
* <a href="#_isobjectvalue">`_.isObject`</a>
* <a href="#_isplainobjectvalue">`_.isPlainObject`</a>
* <a href="#_isregexpvalue">`_.isRegExp`</a>
* <a href="#_isstringvalue">`_.isString`</a>
* <a href="#_istypedarrayvalue">`_.isTypedArray`</a>
* <a href="#_isundefinedvalue">`_.isUndefined`</a>
* <a href="#_toarrayvalue">`_.toArray`</a>
* <a href="#_toplainobjectvalue">`_.toPlainObject`</a>

<!-- /div -->

<!-- div -->

## `Math`
* <a href="#_addaugend-addend">`_.add`</a>
* <a href="#_maxcollection-iteratee-thisarg">`_.max`</a>
* <a href="#_mincollection-iteratee-thisarg">`_.min`</a>
* <a href="#_sumcollection-iteratee-thisarg">`_.sum`</a>

<!-- /div -->

<!-- div -->

## `Number`
* <a href="#_inrangen-start0-end">`_.inRange`</a>
* <a href="#_randommin0-max1-floating">`_.random`</a>

<!-- /div -->

<!-- div -->

## `Object`
* <a href="#_assignobject-sources-customizer-thisarg">`_.assign`</a>
* <a href="#_createprototype-properties">`_.create`</a>
* <a href="#_defaultsobject-sources">`_.defaults`</a>
* <a href="#_assignobject-sources-customizer-thisarg" class="alias">`_.extend` -> `assign`</a>
* <a href="#_findkeyobject-predicate_identity-thisarg">`_.findKey`</a>
* <a href="#_findlastkeyobject-predicate_identity-thisarg">`_.findLastKey`</a>
* <a href="#_forinobject-iteratee_identity-thisarg">`_.forIn`</a>
* <a href="#_forinrightobject-iteratee_identity-thisarg">`_.forInRight`</a>
* <a href="#_forownobject-iteratee_identity-thisarg">`_.forOwn`</a>
* <a href="#_forownrightobject-iteratee_identity-thisarg">`_.forOwnRight`</a>
* <a href="#_functionsobject">`_.functions`</a>
* <a href="#_hasobject-key">`_.has`</a>
* <a href="#_invertobject-multivalue">`_.invert`</a>
* <a href="#_keysobject">`_.keys`</a>
* <a href="#_keysinobject">`_.keysIn`</a>
* <a href="#_mapvaluesobject-iteratee_identity-thisarg">`_.mapValues`</a>
* <a href="#_mergeobject-sources-customizer-thisarg">`_.merge`</a>
* <a href="#_functionsobject" class="alias">`_.methods` -> `functions`</a>
* <a href="#_omitobject-predicate-thisarg">`_.omit`</a>
* <a href="#_pairsobject">`_.pairs`</a>
* <a href="#_pickobject-predicate-thisarg">`_.pick`</a>
* <a href="#_resultobject-key-defaultvalue">`_.result`</a>
* <a href="#_transformobject-iteratee_identity-accumulator-thisarg">`_.transform`</a>
* <a href="#_valuesobject">`_.values`</a>
* <a href="#_valuesinobject">`_.valuesIn`</a>

<!-- /div -->

<!-- div -->

## `String`
* <a href="#_camelcasestring">`_.camelCase`</a>
* <a href="#_capitalizestring">`_.capitalize`</a>
* <a href="#_deburrstring">`_.deburr`</a>
* <a href="#_endswithstring-target-positionstringlength">`_.endsWith`</a>
* <a href="#_escapestring">`_.escape`</a>
* <a href="#_escaperegexpstring">`_.escapeRegExp`</a>
* <a href="#_kebabcasestring">`_.kebabCase`</a>
* <a href="#_padstring-length0-chars">`_.pad`</a>
* <a href="#_padleftstring-length0-chars">`_.padLeft`</a>
* <a href="#_padrightstring-length0-chars">`_.padRight`</a>
* <a href="#_parseintstring-radix">`_.parseInt`</a>
* <a href="#_repeatstring-n0">`_.repeat`</a>
* <a href="#_snakecasestring">`_.snakeCase`</a>
* <a href="#_startcasestring">`_.startCase`</a>
* <a href="#_startswithstring-target-position0">`_.startsWith`</a>
* <a href="#_templatestring-options">`_.template`</a>
* <a href="#_trimstring-charswhitespace">`_.trim`</a>
* <a href="#_trimleftstring-charswhitespace">`_.trimLeft`</a>
* <a href="#_trimrightstring-charswhitespace">`_.trimRight`</a>
* <a href="#_truncstring-options-optionslength30-optionsomission-optionsseparator">`_.trunc`</a>
* <a href="#_unescapestring">`_.unescape`</a>
* <a href="#_wordsstring-pattern">`_.words`</a>

<!-- /div -->

<!-- div -->

## `Utility`
* <a href="#_attemptfunc">`_.attempt`</a>
* <a href="#_callbackfunc_identity-thisarg">`_.callback`</a>
* <a href="#_constantvalue">`_.constant`</a>
* <a href="#_identityvalue">`_.identity`</a>
* <a href="#_callbackfunc_identity-thisarg" class="alias">`_.iteratee` -> `callback`</a>
* <a href="#_matchessource">`_.matches`</a>
* <a href="#_matchespropertykey-value">`_.matchesProperty`</a>
* <a href="#_mixinobjectthis-source-options">`_.mixin`</a>
* <a href="#_noconflict">`_.noConflict`</a>
* <a href="#_noop">`_.noop`</a>
* <a href="#_propertykey">`_.property`</a>
* <a href="#_propertyofobject">`_.propertyOf`</a>
* <a href="#_rangestart0-end-step1">`_.range`</a>
* <a href="#_runincontextcontextroot">`_.runInContext`</a>
* <a href="#_timesn-iteratee_identity-thisarg">`_.times`</a>
* <a href="#_uniqueidprefix">`_.uniqueId`</a>

<!-- /div -->

<!-- div -->

## `Methods`
* <a href="#_templatesettingsimports_">`_.templateSettings.imports._`</a>

<!-- /div -->

<!-- div -->

## `Properties`
* <a href="#_version">`_.VERSION`</a>
* <a href="#_support">`_.support`</a>
* <a href="#_supportargstag">`_.support.argsTag`</a>
* <a href="#_supportenumerrorprops">`_.support.enumErrorProps`</a>
* <a href="#_supportenumprototypes">`_.support.enumPrototypes`</a>
* <a href="#_supportfuncdecomp">`_.support.funcDecomp`</a>
* <a href="#_supportfuncnames">`_.support.funcNames`</a>
* <a href="#_supportnodetag">`_.support.nodeTag`</a>
* <a href="#_supportnonenumshadows">`_.support.nonEnumShadows`</a>
* <a href="#_supportnonenumstrings">`_.support.nonEnumStrings`</a>
* <a href="#_supportownlast">`_.support.ownLast`</a>
* <a href="#_supportspliceobjects">`_.support.spliceObjects`</a>
* <a href="#_supportunindexedchars">`_.support.unindexedChars`</a>
* <a href="#_templatesettings">`_.templateSettings`</a>
* <a href="#_templatesettingsescape">`_.templateSettings.escape`</a>
* <a href="#_templatesettingsevaluate">`_.templateSettings.evaluate`</a>
* <a href="#_templatesettingsimports">`_.templateSettings.imports`</a>
* <a href="#_templatesettingsinterpolate">`_.templateSettings.interpolate`</a>
* <a href="#_templatesettingsvariable">`_.templateSettings.variable`</a>

<!-- /div -->

<!-- /div -->

<!-- div class="doc-container" -->

<!-- div -->

## `“Array” Methods`

<!-- div -->

### <a id="_chunkarray-size1"></a>`_.chunk(array, [size=1])`
<a href="#_chunkarray-size1">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L4536 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.chunk "See the npm package")

Creates an array of elements split into groups the length of `size`.
If `collection` can't be split evenly, the final chunk will be the remaining
elements.

#### Arguments
1. `array` *(Array)*: The array to process.
2. `[size=1]` *(number)*: The length of each chunk.

#### Returns
*(Array)*:  Returns the new array containing chunks.

#### Example
```js
_.chunk(['a', 'b', 'c', 'd'], 2);
// => [['a', 'b'], ['c', 'd']]

_.chunk(['a', 'b', 'c', 'd'], 3);
// => [['a', 'b', 'c'], ['d']]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_compactarray"></a>`_.compact(array)`
<a href="#_compactarray">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L4567 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.compact "See the npm package")

Creates an array with all falsey values removed. The values `false`, `null`,
`0`, `""`, `undefined`, and `NaN` are falsey.

#### Arguments
1. `array` *(Array)*: The array to compact.

#### Returns
*(Array)*:  Returns the new array of filtered values.

#### Example
```js
_.compact([0, 1, false, 2, '', 3]);
// => [1, 2, 3]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_differencearray-values"></a>`_.difference(array, [values])`
<a href="#_differencearray-values">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L4602 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.difference "See the npm package")

Creates an array excluding all values of the provided arrays using
`SameValueZero` for equality comparisons.
<br>
<br>
**Note:** `SameValueZero` comparisons are like strict equality comparisons,
e.g. `===`, except that `NaN` matches `NaN`. See the
[ES spec](https://people.mozilla.org/~jorendorff/es6-draft.html#sec-samevaluezero)
for more details.

#### Arguments
1. `array` *(Array)*: The array to inspect.
2. `[values]` *(...Array)*: The arrays of values to exclude.

#### Returns
*(Array)*:  Returns the new array of filtered values.

#### Example
```js
_.difference([1, 2, 3], [4, 2]);
// => [1, 3]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_droparray-n1"></a>`_.drop(array, [n=1])`
<a href="#_droparray-n1">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L4632 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.drop "See the npm package")

Creates a slice of `array` with `n` elements dropped from the beginning.

#### Arguments
1. `array` *(Array)*: The array to query.
2. `[n=1]` *(number)*: The number of elements to drop.

#### Returns
*(Array)*:  Returns the slice of `array`.

#### Example
```js
_.drop([1, 2, 3]);
// => [2, 3]

_.drop([1, 2, 3], 2);
// => [3]

_.drop([1, 2, 3], 5);
// => []

_.drop([1, 2, 3], 0);
// => [1, 2, 3]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_droprightarray-n1"></a>`_.dropRight(array, [n=1])`
<a href="#_droprightarray-n1">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L4667 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.dropright "See the npm package")

Creates a slice of `array` with `n` elements dropped from the end.

#### Arguments
1. `array` *(Array)*: The array to query.
2. `[n=1]` *(number)*: The number of elements to drop.

#### Returns
*(Array)*:  Returns the slice of `array`.

#### Example
```js
_.dropRight([1, 2, 3]);
// => [1, 2]

_.dropRight([1, 2, 3], 2);
// => [1]

_.dropRight([1, 2, 3], 5);
// => []

_.dropRight([1, 2, 3], 0);
// => [1, 2, 3]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_droprightwhilearray-predicate_identity-thisarg"></a>`_.dropRightWhile(array, [predicate=_.identity], [thisArg])`
<a href="#_droprightwhilearray-predicate_identity-thisarg">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L4728 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.droprightwhile "See the npm package")

Creates a slice of `array` excluding elements dropped from the end.
Elements are dropped until `predicate` returns falsey. The predicate is
bound to `thisArg` and invoked with three arguments: (value, index, array).
<br>
<br>
If a property name is provided for `predicate` the created `_.property`
style callback returns the property value of the given element.
<br>
<br>
If a value is also provided for `thisArg` the created `_.matchesProperty`
style callback returns `true` for elements that have a matching property
value, else `false`.
<br>
<br>
If an object is provided for `predicate` the created `_.matches` style
callback returns `true` for elements that match the properties of the given
object, else `false`.

#### Arguments
1. `array` *(Array)*: The array to query.
2. `[predicate=_.identity]` *(Function|Object|string)*: The function invoked per iteration.
3. `[thisArg]` *(&#42;)*: The `this` binding of `predicate`.

#### Returns
*(Array)*:  Returns the slice of `array`.

#### Example
```js
_.dropRightWhile([1, 2, 3], function(n) {
  return n > 1;
});
// => [1]

var users = [
  { 'user': 'barney',  'active': true },
  { 'user': 'fred',    'active': false },
  { 'user': 'pebbles', 'active': false }
];

// using the `_.matches` callback shorthand
_.pluck(_.dropRightWhile(users, { 'user': 'pebbles', 'active': false }), 'user');
// => ['barney', 'fred']

// using the `_.matchesProperty` callback shorthand
_.pluck(_.dropRightWhile(users, 'active', false), 'user');
// => ['barney']

// using the `_.property` callback shorthand
_.pluck(_.dropRightWhile(users, 'active'), 'user');
// => ['barney', 'fred', 'pebbles']
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_dropwhilearray-predicate_identity-thisarg"></a>`_.dropWhile(array, [predicate=_.identity], [thisArg])`
<a href="#_dropwhilearray-predicate_identity-thisarg">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L4783 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.dropwhile "See the npm package")

Creates a slice of `array` excluding elements dropped from the beginning.
Elements are dropped until `predicate` returns falsey. The predicate is
bound to `thisArg` and invoked with three arguments: (value, index, array).
<br>
<br>
If a property name is provided for `predicate` the created `_.property`
style callback returns the property value of the given element.
<br>
<br>
If a value is also provided for `thisArg` the created `_.matchesProperty`
style callback returns `true` for elements that have a matching property
value, else `false`.
<br>
<br>
If an object is provided for `predicate` the created `_.matches` style
callback returns `true` for elements that have the properties of the given
object, else `false`.

#### Arguments
1. `array` *(Array)*: The array to query.
2. `[predicate=_.identity]` *(Function|Object|string)*: The function invoked per iteration.
3. `[thisArg]` *(&#42;)*: The `this` binding of `predicate`.

#### Returns
*(Array)*:  Returns the slice of `array`.

#### Example
```js
_.dropWhile([1, 2, 3], function(n) {
  return n < 3;
});
// => [3]

var users = [
  { 'user': 'barney',  'active': false },
  { 'user': 'fred',    'active': false },
  { 'user': 'pebbles', 'active': true }
];

// using the `_.matches` callback shorthand
_.pluck(_.dropWhile(users, { 'user': 'barney', 'active': false }), 'user');
// => ['fred', 'pebbles']

// using the `_.matchesProperty` callback shorthand
_.pluck(_.dropWhile(users, 'active', false), 'user');
// => ['pebbles']

// using the `_.property` callback shorthand
_.pluck(_.dropWhile(users, 'active'), 'user');
// => ['barney', 'fred', 'pebbles']
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_fillarray-value-start0-endarraylength"></a>`_.fill(array, value, [start=0], [end=array.length])`
<a href="#_fillarray-value-start0-endarraylength">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L4817 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.fill "See the npm package")

Fills elements of `array` with `value` from `start` up to, but not
including, `end`.
<br>
<br>
**Note:** This method mutates `array`.

#### Arguments
1. `array` *(Array)*: The array to fill.
2. `value` *(&#42;)*: The value to fill `array` with.
3. `[start=0]` *(number)*: The start position.
4. `[end=array.length]` *(number)*: The end position.

#### Returns
*(Array)*:  Returns `array`.

#### Example
```js
var array = [1, 2, 3];

_.fill(array, 'a');
console.log(array);
// => ['a', 'a', 'a']

_.fill(Array(3), 2);
// => [2, 2, 2]

_.fill([4, 6, 8], '*', 1, 2);
// => [4, '*', 8]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_findindexarray-predicate_identity-thisarg"></a>`_.findIndex(array, [predicate=_.identity], [thisArg])`
<a href="#_findindexarray-predicate_identity-thisarg">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L4877 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.findindex "See the npm package")

This method is like `_.find` except that it returns the index of the first
element `predicate` returns truthy for instead of the element itself.
<br>
<br>
If a property name is provided for `predicate` the created `_.property`
style callback returns the property value of the given element.
<br>
<br>
If a value is also provided for `thisArg` the created `_.matchesProperty`
style callback returns `true` for elements that have a matching property
value, else `false`.
<br>
<br>
If an object is provided for `predicate` the created `_.matches` style
callback returns `true` for elements that have the properties of the given
object, else `false`.

#### Arguments
1. `array` *(Array)*: The array to search.
2. `[predicate=_.identity]` *(Function|Object|string)*: The function invoked per iteration.
3. `[thisArg]` *(&#42;)*: The `this` binding of `predicate`.

#### Returns
*(number)*:  Returns the index of the found element, else `-1`.

#### Example
```js
var users = [
  { 'user': 'barney',  'active': false },
  { 'user': 'fred',    'active': false },
  { 'user': 'pebbles', 'active': true }
];

_.findIndex(users, function(chr) {
  return chr.user == 'barney';
});
// => 0

// using the `_.matches` callback shorthand
_.findIndex(users, { 'user': 'fred', 'active': false });
// => 1

// using the `_.matchesProperty` callback shorthand
_.findIndex(users, 'active', false);
// => 0

// using the `_.property` callback shorthand
_.findIndex(users, 'active');
// => 2
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_findlastindexarray-predicate_identity-thisarg"></a>`_.findLastIndex(array, [predicate=_.identity], [thisArg])`
<a href="#_findlastindexarray-predicate_identity-thisarg">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L4927 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.findlastindex "See the npm package")

This method is like `_.findIndex` except that it iterates over elements
of `collection` from right to left.
<br>
<br>
If a property name is provided for `predicate` the created `_.property`
style callback returns the property value of the given element.
<br>
<br>
If a value is also provided for `thisArg` the created `_.matchesProperty`
style callback returns `true` for elements that have a matching property
value, else `false`.
<br>
<br>
If an object is provided for `predicate` the created `_.matches` style
callback returns `true` for elements that have the properties of the given
object, else `false`.

#### Arguments
1. `array` *(Array)*: The array to search.
2. `[predicate=_.identity]` *(Function|Object|string)*: The function invoked per iteration.
3. `[thisArg]` *(&#42;)*: The `this` binding of `predicate`.

#### Returns
*(number)*:  Returns the index of the found element, else `-1`.

#### Example
```js
var users = [
  { 'user': 'barney',  'active': true },
  { 'user': 'fred',    'active': false },
  { 'user': 'pebbles', 'active': false }
];

_.findLastIndex(users, function(chr) {
  return chr.user == 'pebbles';
});
// => 2

// using the `_.matches` callback shorthand
_.findLastIndex(users, { 'user': 'barney', 'active': true });
// => 0

// using the `_.matchesProperty` callback shorthand
_.findLastIndex(users, 'active', false);
// => 2

// using the `_.property` callback shorthand
_.findLastIndex(users, 'active');
// => 0
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_firstarray"></a>`_.first(array)`
<a href="#_firstarray">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L4946 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.first "See the npm package")

Gets the first element of `array`.

#### Aliases
*_.head*

#### Arguments
1. `array` *(Array)*: The array to query.

#### Returns
*(&#42;)*:  Returns the first element of `array`.

#### Example
```js
_.first([1, 2, 3]);
// => 1

_.first([]);
// => undefined
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_flattenarray-isdeep"></a>`_.flatten(array, [isDeep])`
<a href="#_flattenarray-isdeep">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L4970 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.flatten "See the npm package")

Flattens a nested array. If `isDeep` is `true` the array is recursively
flattened, otherwise it is only flattened a single level.

#### Arguments
1. `array` *(Array)*: The array to flatten.
2. `[isDeep]` *(boolean)*: Specify a deep flatten.

#### Returns
*(Array)*:  Returns the new flattened array.

#### Example
```js
_.flatten([1, [2, 3, [4]]]);
// => [1, 2, 3, [4]]

// using `isDeep`
_.flatten([1, [2, 3, [4]]], true);
// => [1, 2, 3, 4]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_flattendeeparray"></a>`_.flattenDeep(array)`
<a href="#_flattendeeparray">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L4991 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.flattendeep "See the npm package")

Recursively flattens a nested array.

#### Arguments
1. `array` *(Array)*: The array to recursively flatten.

#### Returns
*(Array)*:  Returns the new flattened array.

#### Example
```js
_.flattenDeep([1, [2, 3, [4]]]);
// => [1, 2, 3, 4]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_indexofarray-value-fromindex0"></a>`_.indexOf(array, value, [fromIndex=0])`
<a href="#_indexofarray-value-fromindex0">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5028 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.indexof "See the npm package")

Gets the index at which the first occurrence of `value` is found in `array`
using `SameValueZero` for equality comparisons. If `fromIndex` is negative,
it is used as the offset from the end of `array`. If `array` is sorted
providing `true` for `fromIndex` performs a faster binary search.
<br>
<br>
**Note:** `SameValueZero` comparisons are like strict equality comparisons,
e.g. `===`, except that `NaN` matches `NaN`. See the
[ES spec](https://people.mozilla.org/~jorendorff/es6-draft.html#sec-samevaluezero)
for more details.

#### Arguments
1. `array` *(Array)*: The array to search.
2. `value` *(&#42;)*: The value to search for.
3. `[fromIndex=0]` *(boolean|number)*: The index to search from or `true` to perform a binary search on a sorted array.

#### Returns
*(number)*:  Returns the index of the matched value, else `-1`.

#### Example
```js
_.indexOf([1, 2, 1, 2], 2);
// => 1

// using `fromIndex`
_.indexOf([1, 2, 1, 2], 2, 2);
// => 3

// performing a binary search
_.indexOf([1, 1, 2, 2], 2, true);
// => 2
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_initialarray"></a>`_.initial(array)`
<a href="#_initialarray">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5060 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.initial "See the npm package")

Gets all but the last element of `array`.

#### Arguments
1. `array` *(Array)*: The array to query.

#### Returns
*(Array)*:  Returns the slice of `array`.

#### Example
```js
_.initial([1, 2, 3]);
// => [1, 2]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_intersectionarrays"></a>`_.intersection([arrays])`
<a href="#_intersectionarrays">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5082 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.intersection "See the npm package")

Creates an array of unique values in all provided arrays using `SameValueZero`
for equality comparisons.
<br>
<br>
**Note:** `SameValueZero` comparisons are like strict equality comparisons,
e.g. `===`, except that `NaN` matches `NaN`. See the
[ES spec](https://people.mozilla.org/~jorendorff/es6-draft.html#sec-samevaluezero)
for more details.

#### Arguments
1. `[arrays]` *(...Array)*: The arrays to inspect.

#### Returns
*(Array)*:  Returns the new array of shared values.

#### Example
```js
_.intersection([1, 2], [4, 2], [2, 1]);
// => [2]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_lastarray"></a>`_.last(array)`
<a href="#_lastarray">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5137 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.last "See the npm package")

Gets the last element of `array`.

#### Arguments
1. `array` *(Array)*: The array to query.

#### Returns
*(&#42;)*:  Returns the last element of `array`.

#### Example
```js
_.last([1, 2, 3]);
// => 3
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_lastindexofarray-value-fromindexarraylength-1"></a>`_.lastIndexOf(array, value, [fromIndex=array.length-1])`
<a href="#_lastindexofarray-value-fromindexarraylength-1">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5167 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.lastindexof "See the npm package")

This method is like `_.indexOf` except that it iterates over elements of
`array` from right to left.

#### Arguments
1. `array` *(Array)*: The array to search.
2. `value` *(&#42;)*: The value to search for.
3. `[fromIndex=array.length-1]` *(boolean|number)*: The index to search from or `true` to perform a binary search on a sorted array.

#### Returns
*(number)*:  Returns the index of the matched value, else `-1`.

#### Example
```js
_.lastIndexOf([1, 2, 1, 2], 2);
// => 3

// using `fromIndex`
_.lastIndexOf([1, 2, 1, 2], 2, 2);
// => 1

// performing a binary search
_.lastIndexOf([1, 1, 2, 2], 2, true);
// => 3
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_pullarray-values"></a>`_.pull(array, [values])`
<a href="#_pullarray-values">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5218 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.pull "See the npm package")

Removes all provided values from `array` using `SameValueZero` for equality
comparisons.
<br>
<br>
**Notes:**
<br>
* Unlike `_.without`, this method mutates `array`.
<br>
* `SameValueZero` comparisons are like strict equality comparisons, e.g. `===`,
except that `NaN` matches `NaN`. See the [ES spec](https://people.mozilla.org/~jorendorff/es6-draft.html#sec-samevaluezero)
for more details.

#### Arguments
1. `array` *(Array)*: The array to modify.
2. `[values]` *(...&#42;)*: The values to remove.

#### Returns
*(Array)*:  Returns `array`.

#### Example
```js
var array = [1, 2, 3, 1, 2, 3];

_.pull(array, 2, 3);
console.log(array);
// => [1, 1]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_pullatarray-indexes"></a>`_.pullAt(array, [indexes])`
<a href="#_pullatarray-indexes">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5265 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.pullat "See the npm package")

Removes elements from `array` corresponding to the given indexes and returns
an array of the removed elements. Indexes may be specified as an array of
indexes or as individual arguments.
<br>
<br>
**Note:** Unlike `_.at`, this method mutates `array`.

#### Arguments
1. `array` *(Array)*: The array to modify.
2. `[indexes]` *(...(number|number&#91;&#93;)*: The indexes of elements to remove, specified as individual indexes or arrays of indexes.

#### Returns
*(Array)*:  Returns the new array of removed elements.

#### Example
```js
var array = [5, 10, 15, 20];
var evens = _.pullAt(array, 1, 3);

console.log(array);
// => [5, 15]

console.log(evens);
// => [10, 20]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_removearray-predicate_identity-thisarg"></a>`_.remove(array, [predicate=_.identity], [thisArg])`
<a href="#_removearray-predicate_identity-thisarg">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5322 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.remove "See the npm package")

Removes all elements from `array` that `predicate` returns truthy for
and returns an array of the removed elements. The predicate is bound to
`thisArg` and invoked with three arguments: (value, index, array).
<br>
<br>
If a property name is provided for `predicate` the created `_.property`
style callback returns the property value of the given element.
<br>
<br>
If a value is also provided for `thisArg` the created `_.matchesProperty`
style callback returns `true` for elements that have a matching property
value, else `false`.
<br>
<br>
If an object is provided for `predicate` the created `_.matches` style
callback returns `true` for elements that have the properties of the given
object, else `false`.
<br>
<br>
**Note:** Unlike `_.filter`, this method mutates `array`.

#### Arguments
1. `array` *(Array)*: The array to modify.
2. `[predicate=_.identity]` *(Function|Object|string)*: The function invoked per iteration.
3. `[thisArg]` *(&#42;)*: The `this` binding of `predicate`.

#### Returns
*(Array)*:  Returns the new array of removed elements.

#### Example
```js
var array = [1, 2, 3, 4];
var evens = _.remove(array, function(n) {
  return n % 2 == 0;
});

console.log(array);
// => [1, 3]

console.log(evens);
// => [2, 4]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_restarray"></a>`_.rest(array)`
<a href="#_restarray">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5353 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.rest "See the npm package")

Gets all but the first element of `array`.

#### Aliases
*_.tail*

#### Arguments
1. `array` *(Array)*: The array to query.

#### Returns
*(Array)*:  Returns the slice of `array`.

#### Example
```js
_.rest([1, 2, 3]);
// => [2, 3]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_slicearray-start0-endarraylength"></a>`_.slice(array, [start=0], [end=array.length])`
<a href="#_slicearray-start0-endarraylength">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5371 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.slice "See the npm package")

Creates a slice of `array` from `start` up to, but not including, `end`.
<br>
<br>
**Note:** This function is used instead of `Array#slice` to support node
lists in IE < 9 and to ensure dense arrays are returned.

#### Arguments
1. `array` *(Array)*: The array to slice.
2. `[start=0]` *(number)*: The start position.
3. `[end=array.length]` *(number)*: The end position.

#### Returns
*(Array)*:  Returns the slice of `array`.

* * *

<!-- /div -->

<!-- div -->

### <a id="_sortedindexarray-value-iteratee_identity-thisarg"></a>`_.sortedIndex(array, value, [iteratee=_.identity], [thisArg])`
<a href="#_sortedindexarray-value-iteratee_identity-thisarg">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5431 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.sortedindex "See the npm package")

Uses a binary search to determine the lowest index at which `value` should
be inserted into `array` in order to maintain its sort order. If an iteratee
function is provided it is invoked for `value` and each element of `array`
to compute their sort ranking. The iteratee is bound to `thisArg` and
invoked with one argument; (value).
<br>
<br>
If a property name is provided for `iteratee` the created `_.property`
style callback returns the property value of the given element.
<br>
<br>
If a value is also provided for `thisArg` the created `_.matchesProperty`
style callback returns `true` for elements that have a matching property
value, else `false`.
<br>
<br>
If an object is provided for `iteratee` the created `_.matches` style
callback returns `true` for elements that have the properties of the given
object, else `false`.

#### Arguments
1. `array` *(Array)*: The sorted array to inspect.
2. `value` *(&#42;)*: The value to evaluate.
3. `[iteratee=_.identity]` *(Function|Object|string)*: The function invoked per iteration.
4. `[thisArg]` *(&#42;)*: The `this` binding of `iteratee`.

#### Returns
*(number)*:  Returns the index at which `value` should be inserted
into `array`.

#### Example
```js
_.sortedIndex([30, 50], 40);
// => 1

_.sortedIndex([4, 4, 5, 5], 5);
// => 2

var dict = { 'data': { 'thirty': 30, 'forty': 40, 'fifty': 50 } };

// using an iteratee function
_.sortedIndex(['thirty', 'fifty'], 'forty', function(word) {
  return this.data[word];
}, dict);
// => 1

// using the `_.property` callback shorthand
_.sortedIndex([{ 'x': 30 }, { 'x': 50 }], { 'x': 40 }, 'x');
// => 1
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_sortedlastindexarray-value-iteratee_identity-thisarg"></a>`_.sortedLastIndex(array, value, [iteratee=_.identity], [thisArg])`
<a href="#_sortedlastindexarray-value-iteratee_identity-thisarg">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5453 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.sortedlastindex "See the npm package")

This method is like `_.sortedIndex` except that it returns the highest
index at which `value` should be inserted into `array` in order to
maintain its sort order.

#### Arguments
1. `array` *(Array)*: The sorted array to inspect.
2. `value` *(&#42;)*: The value to evaluate.
3. `[iteratee=_.identity]` *(Function|Object|string)*: The function invoked per iteration.
4. `[thisArg]` *(&#42;)*: The `this` binding of `iteratee`.

#### Returns
*(number)*:  Returns the index at which `value` should be inserted
into `array`.

#### Example
```js
_.sortedLastIndex([4, 4, 5, 5], 5);
// => 4
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_takearray-n1"></a>`_.take(array, [n=1])`
<a href="#_takearray-n1">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5479 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.take "See the npm package")

Creates a slice of `array` with `n` elements taken from the beginning.

#### Arguments
1. `array` *(Array)*: The array to query.
2. `[n=1]` *(number)*: The number of elements to take.

#### Returns
*(Array)*:  Returns the slice of `array`.

#### Example
```js
_.take([1, 2, 3]);
// => [1]

_.take([1, 2, 3], 2);
// => [1, 2]

_.take([1, 2, 3], 5);
// => [1, 2, 3]

_.take([1, 2, 3], 0);
// => []
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_takerightarray-n1"></a>`_.takeRight(array, [n=1])`
<a href="#_takerightarray-n1">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5514 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.takeright "See the npm package")

Creates a slice of `array` with `n` elements taken from the end.

#### Arguments
1. `array` *(Array)*: The array to query.
2. `[n=1]` *(number)*: The number of elements to take.

#### Returns
*(Array)*:  Returns the slice of `array`.

#### Example
```js
_.takeRight([1, 2, 3]);
// => [3]

_.takeRight([1, 2, 3], 2);
// => [2, 3]

_.takeRight([1, 2, 3], 5);
// => [1, 2, 3]

_.takeRight([1, 2, 3], 0);
// => []
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_takerightwhilearray-predicate_identity-thisarg"></a>`_.takeRightWhile(array, [predicate=_.identity], [thisArg])`
<a href="#_takerightwhilearray-predicate_identity-thisarg">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5575 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.takerightwhile "See the npm package")

Creates a slice of `array` with elements taken from the end. Elements are
taken until `predicate` returns falsey. The predicate is bound to `thisArg`
and invoked with three arguments: (value, index, array).
<br>
<br>
If a property name is provided for `predicate` the created `_.property`
style callback returns the property value of the given element.
<br>
<br>
If a value is also provided for `thisArg` the created `_.matchesProperty`
style callback returns `true` for elements that have a matching property
value, else `false`.
<br>
<br>
If an object is provided for `predicate` the created `_.matches` style
callback returns `true` for elements that have the properties of the given
object, else `false`.

#### Arguments
1. `array` *(Array)*: The array to query.
2. `[predicate=_.identity]` *(Function|Object|string)*: The function invoked per iteration.
3. `[thisArg]` *(&#42;)*: The `this` binding of `predicate`.

#### Returns
*(Array)*:  Returns the slice of `array`.

#### Example
```js
_.takeRightWhile([1, 2, 3], function(n) {
  return n > 1;
});
// => [2, 3]

var users = [
  { 'user': 'barney',  'active': true },
  { 'user': 'fred',    'active': false },
  { 'user': 'pebbles', 'active': false }
];

// using the `_.matches` callback shorthand
_.pluck(_.takeRightWhile(users, { 'user': 'pebbles', 'active': false }), 'user');
// => ['pebbles']

// using the `_.matchesProperty` callback shorthand
_.pluck(_.takeRightWhile(users, 'active', false), 'user');
// => ['fred', 'pebbles']

// using the `_.property` callback shorthand
_.pluck(_.takeRightWhile(users, 'active'), 'user');
// => []
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_takewhilearray-predicate_identity-thisarg"></a>`_.takeWhile(array, [predicate=_.identity], [thisArg])`
<a href="#_takewhilearray-predicate_identity-thisarg">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5630 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.takewhile "See the npm package")

Creates a slice of `array` with elements taken from the beginning. Elements
are taken until `predicate` returns falsey. The predicate is bound to
`thisArg` and invoked with three arguments: (value, index, array).
<br>
<br>
If a property name is provided for `predicate` the created `_.property`
style callback returns the property value of the given element.
<br>
<br>
If a value is also provided for `thisArg` the created `_.matchesProperty`
style callback returns `true` for elements that have a matching property
value, else `false`.
<br>
<br>
If an object is provided for `predicate` the created `_.matches` style
callback returns `true` for elements that have the properties of the given
object, else `false`.

#### Arguments
1. `array` *(Array)*: The array to query.
2. `[predicate=_.identity]` *(Function|Object|string)*: The function invoked per iteration.
3. `[thisArg]` *(&#42;)*: The `this` binding of `predicate`.

#### Returns
*(Array)*:  Returns the slice of `array`.

#### Example
```js
_.takeWhile([1, 2, 3], function(n) {
  return n < 3;
});
// => [1, 2]

var users = [
  { 'user': 'barney',  'active': false },
  { 'user': 'fred',    'active': false},
  { 'user': 'pebbles', 'active': true }
];

// using the `_.matches` callback shorthand
_.pluck(_.takeWhile(users, { 'user': 'barney', 'active': false }), 'user');
// => ['barney']

// using the `_.matchesProperty` callback shorthand
_.pluck(_.takeWhile(users, 'active', false), 'user');
// => ['barney', 'fred']

// using the `_.property` callback shorthand
_.pluck(_.takeWhile(users, 'active'), 'user');
// => []
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_unionarrays"></a>`_.union([arrays])`
<a href="#_unionarrays">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5655 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.union "See the npm package")

Creates an array of unique values, in order, of the provided arrays using
`SameValueZero` for equality comparisons.
<br>
<br>
**Note:** `SameValueZero` comparisons are like strict equality comparisons,
e.g. `===`, except that `NaN` matches `NaN`. See the
[ES spec](https://people.mozilla.org/~jorendorff/es6-draft.html#sec-samevaluezero)
for more details.

#### Arguments
1. `[arrays]` *(...Array)*: The arrays to inspect.

#### Returns
*(Array)*:  Returns the new array of combined values.

#### Example
```js
_.union([1, 2], [4, 2], [2, 1]);
// => [1, 2, 4]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_uniqarray-issorted-iteratee-thisarg"></a>`_.uniq(array, [isSorted], [iteratee], [thisArg])`
<a href="#_uniqarray-issorted-iteratee-thisarg">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5711 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.uniq "See the npm package")

Creates a duplicate-value-free version of an array using `SameValueZero`
for equality comparisons. Providing `true` for `isSorted` performs a faster
search algorithm for sorted arrays. If an iteratee function is provided it
is invoked for each value in the array to generate the criterion by which
uniqueness is computed. The `iteratee` is bound to `thisArg` and invoked
with three arguments: (value, index, array).
<br>
<br>
If a property name is provided for `iteratee` the created `_.property`
style callback returns the property value of the given element.
<br>
<br>
If a value is also provided for `thisArg` the created `_.matchesProperty`
style callback returns `true` for elements that have a matching property
value, else `false`.
<br>
<br>
If an object is provided for `iteratee` the created `_.matches` style
callback returns `true` for elements that have the properties of the given
object, else `false`.
<br>
<br>
**Note:** `SameValueZero` comparisons are like strict equality comparisons,
e.g. `===`, except that `NaN` matches `NaN`. See the
[ES spec](https://people.mozilla.org/~jorendorff/es6-draft.html#sec-samevaluezero)
for more details.

#### Aliases
*_.unique*

#### Arguments
1. `array` *(Array)*: The array to inspect.
2. `[isSorted]` *(boolean)*: Specify the array is sorted.
3. `[iteratee]` *(Function|Object|string)*: The function invoked per iteration.
4. `[thisArg]` *(&#42;)*: The `this` binding of `iteratee`.

#### Returns
*(Array)*:  Returns the new duplicate-value-free array.

#### Example
```js
_.uniq([1, 2, 1]);
// => [1, 2]

// using `isSorted`
_.uniq([1, 1, 2], true);
// => [1, 2]

// using an iteratee function
_.uniq([1, 2.5, 1.5, 2], function(n) {
  return this.floor(n);
}, Math);
// => [1, 2.5]

// using the `_.property` callback shorthand
_.uniq([{ 'x': 1 }, { 'x': 2 }, { 'x': 1 }], 'x');
// => [{ 'x': 1 }, { 'x': 2 }]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_unziparray"></a>`_.unzip(array)`
<a href="#_unziparray">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5748 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.unzip "See the npm package")

This method is like `_.zip` except that it accepts an array of grouped
elements and creates an array regrouping the elements to their pre-`_.zip`
configuration.

#### Arguments
1. `array` *(Array)*: The array of grouped elements to process.

#### Returns
*(Array)*:  Returns the new array of regrouped elements.

#### Example
```js
var zipped = _.zip(['fred', 'barney'], [30, 40], [true, false]);
// => [['fred', 30, true], ['barney', 40, false]]

_.unzip(zipped);
// => [['fred', 'barney'], [30, 40], [true, false]]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_withoutarray-values"></a>`_.without(array, [values])`
<a href="#_withoutarray-values">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5779 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.without "See the npm package")

Creates an array excluding all provided values using `SameValueZero` for
equality comparisons.
<br>
<br>
**Note:** `SameValueZero` comparisons are like strict equality comparisons,
e.g. `===`, except that `NaN` matches `NaN`. See the
[ES spec](https://people.mozilla.org/~jorendorff/es6-draft.html#sec-samevaluezero)
for more details.

#### Arguments
1. `array` *(Array)*: The array to filter.
2. `[values]` *(...&#42;)*: The values to exclude.

#### Returns
*(Array)*:  Returns the new array of filtered values.

#### Example
```js
_.without([1, 2, 1, 3], 1, 2);
// => [3]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_xorarrays"></a>`_.xor([arrays])`
<a href="#_xorarrays">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5800 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.xor "See the npm package")

Creates an array that is the symmetric difference of the provided arrays.
See [Wikipedia](https://en.wikipedia.org/wiki/Symmetric_difference) for
more details.

#### Arguments
1. `[arrays]` *(...Array)*: The arrays to inspect.

#### Returns
*(Array)*:  Returns the new array of values.

#### Example
```js
_.xor([1, 2], [4, 2]);
// => [1, 4]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_ziparrays"></a>`_.zip([arrays])`
<a href="#_ziparrays">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5830 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.zip "See the npm package")

Creates an array of grouped elements, the first of which contains the first
elements of the given arrays, the second of which contains the second elements
of the given arrays, and so on.

#### Arguments
1. `[arrays]` *(...Array)*: The arrays to process.

#### Returns
*(Array)*:  Returns the new array of grouped elements.

#### Example
```js
_.zip(['fred', 'barney'], [30, 40], [true, false]);
// => [['fred', 30, true], ['barney', 40, false]]
```
* * *

<!-- /div -->

<!-- div -->

### <a id="_zipobjectprops-values"></a>`_.zipObject(props, [values=[]])`
<a href="#_zipobjectprops-values">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L5853 "View in source") [&#x24C9;][1] [&#x24C3;](https://www.npmjs.com/package/lodash.zipobject "See the npm package")

The inverse of `_.pairs`; this method returns an object composed from arrays
of property names and values. Provide either a single two dimensional array,
e.g. `[[key1, value1], [key2, value2]]` or two arrays, one of property names
and one of corresponding values.

#### Aliases
*_.object*

#### Arguments
1. `props` *(Array)*: The property names.
2. `[values=[]]` *(Array)*: The property values.

#### Returns
*(Object)*:  Returns the new object.

#### Example
```js
_.zipObject([['fred', 30], ['barney', 40]]);
// => { 'fred': 30, 'barney': 40 }

_.zipObject(['fred', 'barney'], [30, 40]);
// => { 'fred': 30, 'barney': 40 }
```
* * *

<!-- /div -->

<!-- /div -->

<!-- div -->

## `“Chain” Methods`

<!-- div -->

### <a id="_value"></a>`_(value)`
<a href="#_value">#</a> [&#x24C8;](https://github.com/lodash/lodash/blob/3.6.0/lodash.src.js#L962 "View in source") [&#x24C9;][1]

Creates a `lodash` object which wraps `value` to enable implicit chaining.
Methods that operate on and return arrays, collections, and functions can
be chained together. Methods that return a boolean or single value will
automati