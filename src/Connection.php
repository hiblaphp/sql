<?php 

namespace Hibla\Sql;

use Hibla\Promise\Interfaces\PromiseInterface;

interface Connection
{
    /**
     * Connects to the database.
     * 
     * @return PromiseInterface<self>
     */
    public function connect(): PromiseInterface;

    /**
     * Ping the connection to the database to check if its online
     * 
     * @return PromiseInterface<void>
     */
    public function ping(): PromiseInterface;

 
    /**
     * Execute a select statement to query a database
     * 
     * @return PromiseInterface<QueryResult>
     */
    public function query(): PromiseInterface;

    /**
     * Execute a non Select query to the database like Insert statement
     * 
     * @return PromiseInterface<ExecuteResult>
     */
    public function execute(): PromiseInterface;


    /**
     * Prepare a statement to be executed
     * 
     * @return PromiseInterface<PreparedStatement>
     */
    public function prepare(): PromiseInterface;

    /**
     * Close the connection to the database
     * 
     * @return void
     */
    public function close(): void;
}