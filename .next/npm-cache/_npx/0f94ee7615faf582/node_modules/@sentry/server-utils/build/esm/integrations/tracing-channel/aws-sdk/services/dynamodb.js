import { SPAN_KIND } from '@sentry/core';
import { DB_SYSTEM, DB_NAME, DB_OPERATION, AWS_DYNAMODB_TABLE_NAMES, AWS_DYNAMODB_PROVISIONED_READ_CAPACITY, AWS_DYNAMODB_PROVISIONED_WRITE_CAPACITY, AWS_DYNAMODB_CONSISTENT_READ, AWS_DYNAMODB_PROJECTION, AWS_DYNAMODB_GLOBAL_SECONDARY_INDEXES, AWS_DYNAMODB_LOCAL_SECONDARY_INDEXES, AWS_DYNAMODB_LIMIT, AWS_DYNAMODB_EXCLUSIVE_START_TABLE, AWS_DYNAMODB_SCAN_FORWARD, AWS_DYNAMODB_INDEX_NAME, AWS_DYNAMODB_SELECT, AWS_DYNAMODB_SEGMENT, AWS_DYNAMODB_TOTAL_SEGMENTS, AWS_DYNAMODB_ATTRIBUTE_DEFINITIONS, AWS_DYNAMODB_GLOBAL_SECONDARY_INDEX_UPDATES, AWS_DYNAMODB_CONSUMED_CAPACITY, AWS_DYNAMODB_ITEM_COLLECTION_METRICS, AWS_DYNAMODB_TABLE_COUNT, AWS_DYNAMODB_COUNT, AWS_DYNAMODB_SCANNED_COUNT } from '@sentry/conventions/attributes';
import { DB_SYSTEM_VALUE_DYNAMODB } from '../constants.js';

function toArray(values) {
  return Array.isArray(values) ? values : [values];
}
class DynamodbServiceExtension {
  requestPreSpanHook(normalizedRequest) {
    const operation = normalizedRequest.commandName;
    const tableName = normalizedRequest.commandInput?.TableName;
    const spanAttributes = {};
    spanAttributes[DB_SYSTEM] = DB_SYSTEM_VALUE_DYNAMODB;
    spanAttributes[DB_NAME] = tableName;
    spanAttributes[DB_OPERATION] = operation;
    if (normalizedRequest.commandInput?.TableName) {
      spanAttributes[AWS_DYNAMODB_TABLE_NAMES] = [normalizedRequest.commandInput.TableName];
    } else if (normalizedRequest.commandInput?.RequestItems) {
      spanAttributes[AWS_DYNAMODB_TABLE_NAMES] = Object.keys(normalizedRequest.commandInput.RequestItems);
    }
    if (operation === "CreateTable" || operation === "UpdateTable") {
      if (normalizedRequest.commandInput?.ProvisionedThroughput) {
        spanAttributes[AWS_DYNAMODB_PROVISIONED_READ_CAPACITY] = normalizedRequest.commandInput.ProvisionedThroughput.ReadCapacityUnits;
        spanAttributes[AWS_DYNAMODB_PROVISIONED_WRITE_CAPACITY] = normalizedRequest.commandInput.ProvisionedThroughput.WriteCapacityUnits;
      }
    }
    if (operation === "GetItem" || operation === "Scan" || operation === "Query") {
      if (normalizedRequest.commandInput?.ConsistentRead) {
        spanAttributes[AWS_DYNAMODB_CONSISTENT_READ] = normalizedRequest.commandInput.ConsistentRead;
      }
    }
    if (operation === "Query" || operation === "Scan") {
      if (normalizedRequest.commandInput?.ProjectionExpression) {
        spanAttributes[AWS_DYNAMODB_PROJECTION] = normalizedRequest.commandInput.ProjectionExpression;
      }
    }
    if (operation === "CreateTable") {
      if (normalizedRequest.commandInput?.GlobalSecondaryIndexes) {
        spanAttributes[AWS_DYNAMODB_GLOBAL_SECONDARY_INDEXES] = toArray(
          normalizedRequest.commandInput.GlobalSecondaryIndexes
        ).map((x) => JSON.stringify(x));
      }
      if (normalizedRequest.commandInput?.LocalSecondaryIndexes) {
        spanAttributes[AWS_DYNAMODB_LOCAL_SECONDARY_INDEXES] = toArray(
          normalizedRequest.commandInput.LocalSecondaryIndexes
        ).map((x) => JSON.stringify(x));
      }
    }
    if (operation === "ListTables" || operation === "Query" || operation === "Scan") {
      if (normalizedRequest.commandInput?.Limit) {
        spanAttributes[AWS_DYNAMODB_LIMIT] = normalizedRequest.commandInput.Limit;
      }
    }
    if (operation === "ListTables") {
      if (normalizedRequest.commandInput?.ExclusiveStartTableName) {
        spanAttributes[AWS_DYNAMODB_EXCLUSIVE_START_TABLE] = normalizedRequest.commandInput.ExclusiveStartTableName;
      }
    }
    if (operation === "Query") {
      if (normalizedRequest.commandInput?.ScanIndexForward) {
        spanAttributes[AWS_DYNAMODB_SCAN_FORWARD] = normalizedRequest.commandInput.ScanIndexForward;
      }
      if (normalizedRequest.commandInput?.IndexName) {
        spanAttributes[AWS_DYNAMODB_INDEX_NAME] = normalizedRequest.commandInput.IndexName;
      }
      if (normalizedRequest.commandInput?.Select) {
        spanAttributes[AWS_DYNAMODB_SELECT] = normalizedRequest.commandInput.Select;
      }
    }
    if (operation === "Scan") {
      if (normalizedRequest.commandInput?.Segment) {
        spanAttributes[AWS_DYNAMODB_SEGMENT] = normalizedRequest.commandInput?.Segment;
      }
      if (normalizedRequest.commandInput?.TotalSegments) {
        spanAttributes[AWS_DYNAMODB_TOTAL_SEGMENTS] = normalizedRequest.commandInput?.TotalSegments;
      }
      if (normalizedRequest.commandInput?.IndexName) {
        spanAttributes[AWS_DYNAMODB_INDEX_NAME] = normalizedRequest.commandInput.IndexName;
      }
      if (normalizedRequest.commandInput?.Select) {
        spanAttributes[AWS_DYNAMODB_SELECT] = normalizedRequest.commandInput.Select;
      }
    }
    if (operation === "UpdateTable") {
      if (normalizedRequest.commandInput?.AttributeDefinitions) {
        spanAttributes[AWS_DYNAMODB_ATTRIBUTE_DEFINITIONS] = toArray(
          normalizedRequest.commandInput.AttributeDefinitions
        ).map((x) => JSON.stringify(x));
      }
      if (normalizedRequest.commandInput?.GlobalSecondaryIndexUpdates) {
        spanAttributes[AWS_DYNAMODB_GLOBAL_SECONDARY_INDEX_UPDATES] = toArray(
          normalizedRequest.commandInput.GlobalSecondaryIndexUpdates
        ).map((x) => JSON.stringify(x));
      }
    }
    return {
      spanAttributes,
      spanKind: SPAN_KIND.CLIENT,
      // Matches what the exporter infers from `db.system` for the OTel DynamoDB spans.
      spanOp: "db"
    };
  }
  responseHook(response, span) {
    if (response.data?.ConsumedCapacity) {
      span.setAttribute(
        AWS_DYNAMODB_CONSUMED_CAPACITY,
        toArray(response.data.ConsumedCapacity).map((x) => JSON.stringify(x))
      );
    }
    if (response.data?.ItemCollectionMetrics) {
      span.setAttribute(
        AWS_DYNAMODB_ITEM_COLLECTION_METRICS,
        toArray(response.data.ItemCollectionMetrics).map((x) => JSON.stringify(x))
      );
    }
    if (response.data?.TableNames) {
      span.setAttribute(AWS_DYNAMODB_TABLE_COUNT, response.data?.TableNames.length);
    }
    if (response.data?.Count) {
      span.setAttribute(AWS_DYNAMODB_COUNT, response.data?.Count);
    }
    if (response.data?.ScannedCount) {
      span.setAttribute(AWS_DYNAMODB_SCANNED_COUNT, response.data?.ScannedCount);
    }
  }
}

export { DynamodbServiceExtension };
//# sourceMappingURL=dynamodb.js.map
