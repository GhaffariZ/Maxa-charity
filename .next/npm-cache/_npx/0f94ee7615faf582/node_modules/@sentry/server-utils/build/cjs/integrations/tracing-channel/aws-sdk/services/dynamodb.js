Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });

const core = require('@sentry/core');
const attributes = require('@sentry/conventions/attributes');
const constants = require('../constants.js');

function toArray(values) {
  return Array.isArray(values) ? values : [values];
}
class DynamodbServiceExtension {
  requestPreSpanHook(normalizedRequest) {
    const operation = normalizedRequest.commandName;
    const tableName = normalizedRequest.commandInput?.TableName;
    const spanAttributes = {};
    spanAttributes[attributes.DB_SYSTEM] = constants.DB_SYSTEM_VALUE_DYNAMODB;
    spanAttributes[attributes.DB_NAME] = tableName;
    spanAttributes[attributes.DB_OPERATION] = operation;
    if (normalizedRequest.commandInput?.TableName) {
      spanAttributes[attributes.AWS_DYNAMODB_TABLE_NAMES] = [normalizedRequest.commandInput.TableName];
    } else if (normalizedRequest.commandInput?.RequestItems) {
      spanAttributes[attributes.AWS_DYNAMODB_TABLE_NAMES] = Object.keys(normalizedRequest.commandInput.RequestItems);
    }
    if (operation === "CreateTable" || operation === "UpdateTable") {
      if (normalizedRequest.commandInput?.ProvisionedThroughput) {
        spanAttributes[attributes.AWS_DYNAMODB_PROVISIONED_READ_CAPACITY] = normalizedRequest.commandInput.ProvisionedThroughput.ReadCapacityUnits;
        spanAttributes[attributes.AWS_DYNAMODB_PROVISIONED_WRITE_CAPACITY] = normalizedRequest.commandInput.ProvisionedThroughput.WriteCapacityUnits;
      }
    }
    if (operation === "GetItem" || operation === "Scan" || operation === "Query") {
      if (normalizedRequest.commandInput?.ConsistentRead) {
        spanAttributes[attributes.AWS_DYNAMODB_CONSISTENT_READ] = normalizedRequest.commandInput.ConsistentRead;
      }
    }
    if (operation === "Query" || operation === "Scan") {
      if (normalizedRequest.commandInput?.ProjectionExpression) {
        spanAttributes[attributes.AWS_DYNAMODB_PROJECTION] = normalizedRequest.commandInput.ProjectionExpression;
      }
    }
    if (operation === "CreateTable") {
      if (normalizedRequest.commandInput?.GlobalSecondaryIndexes) {
        spanAttributes[attributes.AWS_DYNAMODB_GLOBAL_SECONDARY_INDEXES] = toArray(
          normalizedRequest.commandInput.GlobalSecondaryIndexes
        ).map((x) => JSON.stringify(x));
      }
      if (normalizedRequest.commandInput?.LocalSecondaryIndexes) {
        spanAttributes[attributes.AWS_DYNAMODB_LOCAL_SECONDARY_INDEXES] = toArray(
          normalizedRequest.commandInput.LocalSecondaryIndexes
        ).map((x) => JSON.stringify(x));
      }
    }
    if (operation === "ListTables" || operation === "Query" || operation === "Scan") {
      if (normalizedRequest.commandInput?.Limit) {
        spanAttributes[attributes.AWS_DYNAMODB_LIMIT] = normalizedRequest.commandInput.Limit;
      }
    }
    if (operation === "ListTables") {
      if (normalizedRequest.commandInput?.ExclusiveStartTableName) {
        spanAttributes[attributes.AWS_DYNAMODB_EXCLUSIVE_START_TABLE] = normalizedRequest.commandInput.ExclusiveStartTableName;
      }
    }
    if (operation === "Query") {
      if (normalizedRequest.commandInput?.ScanIndexForward) {
        spanAttributes[attributes.AWS_DYNAMODB_SCAN_FORWARD] = normalizedRequest.commandInput.ScanIndexForward;
      }
      if (normalizedRequest.commandInput?.IndexName) {
        spanAttributes[attributes.AWS_DYNAMODB_INDEX_NAME] = normalizedRequest.commandInput.IndexName;
      }
      if (normalizedRequest.commandInput?.Select) {
        spanAttributes[attributes.AWS_DYNAMODB_SELECT] = normalizedRequest.commandInput.Select;
      }
    }
    if (operation === "Scan") {
      if (normalizedRequest.commandInput?.Segment) {
        spanAttributes[attributes.AWS_DYNAMODB_SEGMENT] = normalizedRequest.commandInput?.Segment;
      }
      if (normalizedRequest.commandInput?.TotalSegments) {
        spanAttributes[attributes.AWS_DYNAMODB_TOTAL_SEGMENTS] = normalizedRequest.commandInput?.TotalSegments;
      }
      if (normalizedRequest.commandInput?.IndexName) {
        spanAttributes[attributes.AWS_DYNAMODB_INDEX_NAME] = normalizedRequest.commandInput.IndexName;
      }
      if (normalizedRequest.commandInput?.Select) {
        spanAttributes[attributes.AWS_DYNAMODB_SELECT] = normalizedRequest.commandInput.Select;
      }
    }
    if (operation === "UpdateTable") {
      if (normalizedRequest.commandInput?.AttributeDefinitions) {
        spanAttributes[attributes.AWS_DYNAMODB_ATTRIBUTE_DEFINITIONS] = toArray(
          normalizedRequest.commandInput.AttributeDefinitions
        ).map((x) => JSON.stringify(x));
      }
      if (normalizedRequest.commandInput?.GlobalSecondaryIndexUpdates) {
        spanAttributes[attributes.AWS_DYNAMODB_GLOBAL_SECONDARY_INDEX_UPDATES] = toArray(
          normalizedRequest.commandInput.GlobalSecondaryIndexUpdates
        ).map((x) => JSON.stringify(x));
      }
    }
    return {
      spanAttributes,
      spanKind: core.SPAN_KIND.CLIENT,
      // Matches what the exporter infers from `db.system` for the OTel DynamoDB spans.
      spanOp: "db"
    };
  }
  responseHook(response, span) {
    if (response.data?.ConsumedCapacity) {
      span.setAttribute(
        attributes.AWS_DYNAMODB_CONSUMED_CAPACITY,
        toArray(response.data.ConsumedCapacity).map((x) => JSON.stringify(x))
      );
    }
    if (response.data?.ItemCollectionMetrics) {
      span.setAttribute(
        attributes.AWS_DYNAMODB_ITEM_COLLECTION_METRICS,
        toArray(response.data.ItemCollectionMetrics).map((x) => JSON.stringify(x))
      );
    }
    if (response.data?.TableNames) {
      span.setAttribute(attributes.AWS_DYNAMODB_TABLE_COUNT, response.data?.TableNames.length);
    }
    if (response.data?.Count) {
      span.setAttribute(attributes.AWS_DYNAMODB_COUNT, response.data?.Count);
    }
    if (response.data?.ScannedCount) {
      span.setAttribute(attributes.AWS_DYNAMODB_SCANNED_COUNT, response.data?.ScannedCount);
    }
  }
}

exports.DynamodbServiceExtension = DynamodbServiceExtension;
//# sourceMappingURL=dynamodb.js.map
