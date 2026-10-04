Object.defineProperties(exports, { __esModule: { value: true }, [Symbol.toStringTag]: { value: 'Module' } });

const sourceMap = require('../../_virtual/source-map.js');
require('./lib/source-map-generator.js');
require('./lib/source-map-consumer.js');
require('./lib/source-node.js');
const sourceMapGenerator = require('../../_virtual/source-map-generator.js');
const sourceMapConsumer = require('../../_virtual/source-map-consumer.js');
const sourceNode = require('../../_virtual/source-node.js');

sourceMap.__exports.SourceMapGenerator = sourceMapGenerator.__exports.SourceMapGenerator;
sourceMap.__exports.SourceMapConsumer = sourceMapConsumer.__exports.SourceMapConsumer;
sourceMap.__exports.SourceNode = sourceNode.__exports.SourceNode;

exports.default = sourceMap.__exports;
//# sourceMappingURL=source-map.js.map
