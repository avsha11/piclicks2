<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to design_collage table
        Schema::table('design_collage', function (Blueprint $table) {
            if (!$this->hasIndex('design_collage', 'unique_id')) {
                $table->index('unique_id', 'idx_design_collage_unique_id');
            }
            if (!$this->hasIndex('design_collage', 'is_deleted')) {
                $table->index('is_deleted', 'idx_design_collage_is_deleted');
            }
            if (!$this->hasIndex('design_collage', 'empty')) {
                $table->index('empty', 'idx_design_collage_empty');
            }
            if (!$this->hasIndex('design_collage', ['unique_id', 'is_deleted', 'empty'])) {
                $table->index(['unique_id', 'is_deleted', 'empty'], 'idx_design_collage_composite');
            }
        });

        // Add indexes to design_collage_master table
        Schema::table('design_collage_master', function (Blueprint $table) {
            if (!$this->hasIndex('design_collage_master', 'unique_id')) {
                $table->index('unique_id', 'idx_design_collage_master_unique_id');
            }
            if (!$this->hasIndex('design_collage_master', 'user_type')) {
                $table->index('user_type', 'idx_design_collage_master_user_type');
            }
            if (!$this->hasIndex('design_collage_master', 'status')) {
                $table->index('status', 'idx_design_collage_master_status');
            }
            if (!$this->hasIndex('design_collage_master', ['user_type', 'status', 'user_id'])) {
                $table->index(['user_type', 'status', 'user_id'], 'idx_design_collage_master_composite');
            }
        });

        // Add indexes to carts table
        Schema::table('carts', function (Blueprint $table) {
            if (!$this->hasIndex('carts', 'user_id')) {
                $table->index('user_id', 'idx_carts_user_id');
            }
            if (!$this->hasIndex('carts', 'product_id')) {
                $table->index('product_id', 'idx_carts_product_id');
            }
        });

        // Add indexes to artgallery_favorites table
        Schema::table('artgallery_favorites', function (Blueprint $table) {
            if (!$this->hasIndex('artgallery_favorites', 'unique_id')) {
                $table->index('unique_id', 'idx_artgallery_favorites_unique_id');
            }
            if (!$this->hasIndex('artgallery_favorites', 'user_id')) {
                $table->index('user_id', 'idx_artgallery_favorites_user_id');
            }
            if (!$this->hasIndex('artgallery_favorites', 'guest_id')) {
                $table->index('guest_id', 'idx_artgallery_favorites_guest_id');
            }
        });

        // Add indexes to countries table
        Schema::table('countries', function (Blueprint $table) {
            if (!$this->hasIndex('countries', 'code')) {
                $table->index('code', 'idx_countries_code');
            }
        });

        // Add indexes to design_collage_admins table
        Schema::table('design_collage_admins', function (Blueprint $table) {
            if (!$this->hasIndex('design_collage_admins', 'unique_id')) {
                $table->index('unique_id', 'idx_design_collage_admins_unique_id');
            }
            if (!$this->hasIndex('design_collage_admins', 'collection_id')) {
                $table->index('collection_id', 'idx_design_collage_admins_collection_id');
            }
        });

        // Add indexes to orders table (if not already present)
        Schema::table('orders', function (Blueprint $table) {
            if (!$this->hasIndex('orders', 'order_status')) {
                $table->index('order_status', 'idx_orders_order_status');
            }
            if (!$this->hasIndex('orders', 'created_at')) {
                $table->index('created_at', 'idx_orders_created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_collage', function (Blueprint $table) {
            $table->dropIndex('idx_design_collage_unique_id');
            $table->dropIndex('idx_design_collage_is_deleted');
            $table->dropIndex('idx_design_collage_empty');
            $table->dropIndex('idx_design_collage_composite');
        });

        Schema::table('design_collage_master', function (Blueprint $table) {
            $table->dropIndex('idx_design_collage_master_unique_id');
            $table->dropIndex('idx_design_collage_master_user_type');
            $table->dropIndex('idx_design_collage_master_status');
            $table->dropIndex('idx_design_collage_master_composite');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex('idx_carts_user_id');
            $table->dropIndex('idx_carts_product_id');
        });

        Schema::table('artgallery_favorites', function (Blueprint $table) {
            $table->dropIndex('idx_artgallery_favorites_unique_id');
            $table->dropIndex('idx_artgallery_favorites_user_id');
            $table->dropIndex('idx_artgallery_favorites_guest_id');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex('idx_countries_code');
        });

        Schema::table('design_collage_admins', function (Blueprint $table) {
            $table->dropIndex('idx_design_collage_admins_unique_id');
            $table->dropIndex('idx_design_collage_admins_collection_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_order_status');
            $table->dropIndex('idx_orders_created_at');
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function hasIndex(string $table, $columns): bool
    {
        try {
            $connection = Schema::getConnection();
            $doctrineSchemaManager = $connection->getDoctrineSchemaManager();
            $doctrineTable = $doctrineSchemaManager->listTableDetails($table);
            
            $indexName = is_array($columns) 
                ? 'idx_' . $table . '_' . implode('_', $columns)
                : 'idx_' . $table . '_' . $columns;
            
            return $doctrineTable->hasIndex($indexName);
        } catch (\Exception $e) {
            // If table doesn't exist or error occurs, assume index doesn't exist
            return false;
        }
    }
};

