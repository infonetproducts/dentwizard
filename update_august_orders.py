import pandas as pd
from datetime import datetime

print("="*60)
print("August Orders - Item Title Updater")
print("="*60)

# Load the reference file with Item IDs and Titles
print("\n1. Loading reference file with Item IDs and Titles...")
ref_file = r'C:\Download\item_titles_with_titles_20250923_142453.xlsx'
ref_df = pd.read_excel(ref_file)
print(f"   Loaded {len(ref_df)} items from reference file")
print(f"   Columns: {ref_df.columns.tolist()}")

# Load the August Orders file
print("\n2. Loading August Orders file...")
orders_file = r'C:\Download\August_Orders.xlsx'
orders_df = pd.read_excel(orders_file)
print(f"   Loaded {len(orders_df)} rows from August Orders")
print(f"   Columns: {orders_df.columns.tolist()}")

# Create a dictionary for fast lookup (ItemID -> Title)
print("\n3. Creating lookup dictionary...")
title_lookup = {}
for idx, row in ref_df.iterrows():
    if pd.notna(row['ItemID']) and pd.notna(row['Title']):
        # Convert ItemID to string for consistent matching
        item_id = str(int(float(row['ItemID'])))
        title_lookup[item_id] = row['Title']
print(f"   Created lookup for {len(title_lookup)} Item IDs")

# Find the column with Item IDs in the orders file
print("\n4. Identifying Item ID column in August Orders...")
possible_id_columns = ['ItemID', 'Item ID', 'Item_ID', 'ID', 'ProductID', 'Product ID']
id_column = None
for col in possible_id_columns:
    if col in orders_df.columns:
        id_column = col
        break

if not id_column:
    # Check if any column contains mostly numeric values that could be IDs
    for col in orders_df.columns:
        if orders_df[col].dtype in ['int64', 'float64', 'object']:
            sample = orders_df[col].dropna().head(10)
            try:
                # Check if values look like Item IDs (numeric, in reasonable range)
                numeric_vals = pd.to_numeric(sample, errors='coerce')
                if numeric_vals.notna().sum() > 5:  # Most are numeric
                    print(f"   Found potential ID column: {col}")
                    user_confirm = input(f"   Is '{col}' the Item ID column? (y/n): ")
                    if user_confirm.lower() == 'y':
                        id_column = col
                        break
            except:
                pass

if not id_column:
    print("\n   ERROR: Could not identify Item ID column in August Orders")
    print("   Available columns:", orders_df.columns.tolist())
    exit(1)

print(f"   Using column: '{id_column}' for Item IDs")

# Find or create title column
print("\n5. Preparing Title column in August Orders...")
title_column = None
for col in ['Title', 'Item Title', 'ItemTitle', 'Product Title', 'ProductTitle', 'Description']:
    if col in orders_df.columns:
        title_column = col
        print(f"   Found existing title column: '{title_column}'")
        break

if not title_column:
    title_column = 'Item Title'
    orders_df[title_column] = ''
    print(f"   Created new column: '{title_column}'")

# Update titles
print("\n6. Updating titles...")
updated_count = 0
not_found_ids = []

for idx in range(len(orders_df)):
    item_id = orders_df.at[idx, id_column]
    
    if pd.notna(item_id):
        # Convert to string for lookup
        item_id_str = str(int(float(item_id))) if isinstance(item_id, (int, float)) else str(item_id)
        
        if item_id_str in title_lookup:
            new_title = title_lookup[item_id_str]
            orders_df.at[idx, title_column] = new_title
            updated_count += 1
            
            # Show first few updates
            if updated_count <= 5:
                print(f"   Updated ID {item_id_str}: {new_title[:50]}...")
        else:
            if item_id_str not in not_found_ids:
                not_found_ids.append(item_id_str)

# Save the updated file
timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
output_file = rf'C:\Download\August_Orders_Updated_{timestamp}.xlsx'
print(f"\n7. Saving updated file...")
orders_df.to_excel(output_file, index=False)

# Summary
print(f"\n{'='*60}")
print("SUMMARY:")
print(f"  Total rows in August Orders: {len(orders_df)}")
print(f"  Titles updated: {updated_count}")
print(f"  Rows without updates: {len(orders_df) - updated_count}")
if not_found_ids:
    print(f"  Unique Item IDs not found in reference: {len(not_found_ids)}")
    if len(not_found_ids) <= 10:
        print(f"    IDs not found: {not_found_ids}")
print(f"\nOutput saved to: {output_file}")
print("="*60)

# Show sample of updated data
print("\nSample of updated orders (first 10 with titles):")
sample = orders_df[orders_df[title_column] != ''].head(10)
if not sample.empty:
    for idx, row in sample.iterrows():
        print(f"  ID {row[id_column]}: {row[title_column][:50]}...")