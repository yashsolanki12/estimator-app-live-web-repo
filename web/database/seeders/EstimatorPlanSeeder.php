<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstimatorPlan;
use Illuminate\Support\Facades\Schema;

class EstimatorPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Schema::hasTable('estimator_plans')) { 
            EstimatorPlan::truncate();
            
            $data = [
                [
                    'name'  => 'Free',
                    'type'  => 'free',
                    'short_description' => 'Up to 3000 Monthly Views',
                    'price' => 0,
                    'limit' => env('FREE_PLAN_LIMIT',3000),
                    'status'=> 1,
                ],
                [
                    'name'  => '$4.99/Month',
                    'type'  => 'paid',
                    'short_description' => 'Up to 15,000 Monthly Views',
                    'price' => 4.99,
                    'limit' => 15000,
                    'status'=> 1,
                ],
                [
                    'name'  => '$9.99/Month',
                    'type'  => 'paid',
                    'short_description' => 'Unlimited Monthly Views',
                    'price' => 9.99,
                    'limit' => null,
                    'status'=> 1,
                ]
            ];

            foreach ($data as $value) {
                $estPlan = new EstimatorPlan();
                $estPlan->name = $value['name'];
                $estPlan->type = $value['type'];
                $estPlan->short_description = $value['short_description'];
                $estPlan->price = $value['price'];
                $estPlan->limit = $value['limit'];
                $estPlan->status = $value['status'];
                $estPlan->save();
            }
        }
    }
}
