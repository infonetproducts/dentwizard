<style>


/* Mobile Budget Balance Styles - Hidden by default */
#mobile-budget-balance {
    display: none;
}

.budget-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
    font-weight: 600;
}

.budget-label {
    color: #666;
    margin-right: 10px;
}

.budget-amount {
    color: #808080;
    font-weight: 700;
    background: #fff;
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.mobile-only {
    display: none;
}




@media only screen and (max-width: 767px) {
    /* Show mobile budget balance */
    .mobile-only {
        display: block !important;
    }
    
    #mobile-budget-balance {
        display: block;
        margin: 15px 0 10px 0;
        padding: 12px 15px;
        background: linear-gradient(135deg, #f8f8f8 0%, #fff 100%);
        border-radius: 8px;
        border: 1px solid #e5e5e5;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
    }
    
    .budget-info {
        background: transparent;
    }
    
    .budget-amount {
        font-size: 16px;
        padding: 6px 12px;
        background: #808080;
        color: #fff;
        border: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
}



@media only screen and (max-width: 479px) {
    #mobile-budget-balance {
        margin: 10px 0;
    }
    
    .budget-info {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 8px;
    }
    
    .budget-label {
        margin-bottom: 0;
        margin-right: 0;
        font-size: 13px;
    }
    
    .budget-amount {
        font-size: 15px;
        padding: 8px 15px;
    }
}





.container .four.columns {
    width: 210px !important;
   
}




</style>