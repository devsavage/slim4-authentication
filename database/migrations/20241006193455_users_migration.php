<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UsersMigration extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table("users");
        $table->addColumn("email", "string", [
            "length" => 75,
            "null" => false
        ])
        ->addIndex("email", [
            "unique" => true,
            "name" => "email"
        ])
        ->addColumn("password", "string", [
            "length" => 255
        ])
        ->addColumn("verify_token", "string", [
            "length" => 255
        ])
        ->addColumn("verified", "boolean", [
            "default" => false,
            "null" => false
        ])
        ->addColumn("verify_started_at", "timestamp")
        ->addColumn("remember_identifier", "string", [
            "length" => 255
        ])
        ->addColumn("remember_token", "string", [
            "length" => 255
        ])->addTimestamps()->create();
    }
}
