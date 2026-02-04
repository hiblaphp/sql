# Hibla\Sql

Common SQL contracts and utilities for Hibla database clients.

## Overview

`Hibla\Sql` is a foundational package that provides standardized interfaces, exceptions, and utilities for building asynchronous SQL database clients in the Hibla ecosystem. It establishes a common contract that enables interchangeable database implementations while maintaining type safety and consistent developer experience.

## Features

- **Unified Interface**: Common `SqlClientInterface` for all database clients (MySQL, PostgreSQL, etc.)
- **Type-Safe Results**: Generic `Result` interface for query results with full metadata
- **Transaction Support**: Standardized transaction isolation levels and interfaces
- **Exception Hierarchy**: Comprehensive exception types for robust error handling
- **Promise-Based**: Built on `Hibla\Promise` for fully asynchronous operations
- **Connection Pooling**: Contracts for efficient connection pool management

## Installation
```bash
composer require hiblaphp/sql
```
