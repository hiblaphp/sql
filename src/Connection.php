<?php 

namespace Hibla\Sql;

use Hibla\Promise\Interfaces\PromiseInterface;

interface Connection
{
    /**
     * Ping the connection to the database to check if its online
     * 
     * @return PromiseInterface<void>
     */
    public function ping(): PromiseInterface;

 
    /**
     * Execute an sql query a database
     * 
     * @return PromiseInterface<Result>
     */
    public function query(): PromiseInterface;


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