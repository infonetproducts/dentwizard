------------------------------------
OH State : 

Array
(
    [tax] => Array
        (
            [amount_to_collect] => 12.48
            [breakdown] => Array
                (
                    [city_tax_collectable] => 0
                    [city_tax_rate] => 0
                    [city_taxable_amount] => 0
                    [combined_tax_rate] => 0.078
                    [county_tax_collectable] => 2
                    [county_tax_rate] => 0.0125
                    [county_taxable_amount] => 160
                    [line_items] => Array
                        (
                            [0] => Array
                                (
                                    [city_amount] => 0
                                    [city_tax_rate] => 0
                                    [city_taxable_amount] => 0
                                    [combined_tax_rate] => 0.078
                                    [county_amount] => 1.88
                                    [county_tax_rate] => 0.0125
                                    [county_taxable_amount] => 150
                                    [id] => 1
                                    [special_district_amount] => 1.2
                                    [special_district_taxable_amount] => 150
                                    [special_tax_rate] => 0.008
                                    [state_amount] => 8.63
                                    [state_sales_tax_rate] => 0.0575
                                    [state_taxable_amount] => 150
                                    [tax_collectable] => 11.7
                                    [taxable_amount] => 150
                                )

                        )

                    [shipping] => Array
                        (
                            [city_amount] => 0
                            [city_tax_rate] => 0
                            [city_taxable_amount] => 0
                            [combined_tax_rate] => 0.078
                            [county_amount] => 0.13
                            [county_tax_rate] => 0.0125
                            [county_taxable_amount] => 10
                            [special_district_amount] => 0.08
                            [special_tax_rate] => 0.008
                            [special_taxable_amount] => 10
                            [state_amount] => 0.58
                            [state_sales_tax_rate] => 0.0575
                            [state_taxable_amount] => 10
                            [tax_collectable] => 0.78
                            [taxable_amount] => 10
                        )

                    [special_district_tax_collectable] => 1.28
                    [special_district_taxable_amount] => 160
                    [special_tax_rate] => 0.008
                    [state_tax_collectable] => 9.2
                    [state_tax_rate] => 0.0575
                    [state_taxable_amount] => 160
                    [tax_collectable] => 12.48
                    [taxable_amount] => 160
                )

            [freight_taxable] => 1
            [has_nexus] => 1
            [jurisdictions] => Array
                (
                    [city] => CINCINNATI
                    [country] => US
                    [county] => HAMILTON
                    [state] => OH
                )

            [order_total_amount] => 160
            [rate] => 0.078
            [shipping] => 10
            [tax_source] => origin
            [taxable_amount] => 160
        )

)

------------------------------------
Passing array : 

Array
(
    [from_country] => US
    [from_zip] => 45202
    [from_state] => OH
    [to_country] => US
    [to_zip] => 43001
    [to_state] => OH
    [amount] => 40.00
    [shipping] => 0
    [nexus_addresses] => Array
        (
            [0] => Array
                (
                    [id] => Leader-Graphics-OH
                    [country] => US
                    [zip] => 45202
                    [state] => OH
                )

        )

    [line_items] => Array
        (
            [0] => Array
                (
                    [id] => 1
                    [quantity] => 1
                    [product_tax_code] => 20010
                    [unit_price] => 150
                    [discount] => 0
                )

        )

)
------------------------------------