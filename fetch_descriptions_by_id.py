import pandas as pd
import requests
import time
from datetime import datetime

print("="*60)
print("Item Description Fetcher (Using Item IDs)")
print("="*60)

# Load the Excel file
print("Loading Excel file...")
df = pd.read_excel(r'C:\Download\item_titles.xlsx')
print(f"Loaded {len(df)} items")
print(f"Columns found: {df.columns.tolist()}")

# Check what column names we have
if 'Item ID' in df.columns:
    id_column = 'Item ID'
elif 'ItemID' in df.columns:
    id_column = 'ItemID'
elif 'ID' in df.columns:
    id_column = 'ID'
else:
    print("\nERROR: No Item ID column found!")
    print("Available columns:", df.columns.tolist())
    print("Please ensure your Excel has a column named 'Item ID', 'ItemID', or 'ID'")
    exit(1)

print(f"Using column: '{id_column}' for Item IDs")

# Add description column if it doesn't exist
if 'Description' not in df.columns:
    df['Description'] = ''

# Function to get description from API
def get_description(item_id):
    try:
        # Convert to string and remove any decimal points if it's a number
        item_id = str(int(float(str(item_id)))) if pd.notna(item_id) else ''
        
        url = f'https://dentwizard.lgstore.com/lg/API/v1/products/get-item-description.php?item_id={item_id}'
        response = requests.get(url, timeout=5)
        if response.status_code == 200:
            data = response.json()
            if data.get('success'):
                return data['data'].get('description', '')
    except Exception as e:
        print(f"  Error for ID {item_id}: {str(e)}")
    return ''

# Show sample of Item IDs
print(f"\nSample Item IDs from file:")
sample_ids = df[id_column].head(10).tolist()
for sid in sample_ids:
    print(f"  - {sid}")

# Process all items
print(f"\nFetching descriptions from database...")
print("This will take approximately {:.1f} minutes".format(len(df) * 0.1 / 60))

success_count = 0
batch_size = 25
total = len(df)

for i in range(0, total, batch_size):
    batch_end = min(i + batch_size, total)
    print(f"\nProcessing batch {i//batch_size + 1} (items {i+1}-{batch_end} of {total})...")
    
    for idx in range(i, batch_end):
        item_id = df.at[idx, id_column]
        
        # Skip if no ID
        if pd.isna(item_id):
            continue
            
        desc = get_description(item_id)
        
        if desc:
            df.at[idx, 'Description'] = desc
            success_count += 1
            # Show first few successes
            if success_count <= 5:
                print(f"  [OK] ID {item_id}: {desc[:60]}{'...' if len(desc) > 60 else ''}")
        else:
            df.at[idx, 'Description'] = ''
    
    # Small delay between batches
    time.sleep(0.5)

# Save results
timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
output_file = rf'C:\Download\item_titles_with_descriptions_{timestamp}.xlsx'
print(f"\n{'='*60}")
print(f"Saving results to: {output_file}")
df.to_excel(output_file, index=False)

# Summary
print(f"\n{'='*60}")
print(f"SUMMARY:")
print(f"  Total items processed: {total}")
print(f"  Descriptions found: {success_count}")
print(f"  Missing descriptions: {total - success_count}")
print(f"  Success rate: {(success_count/total)*100:.1f}%")
print(f"\nOutput saved to: {output_file}")
print("="*60)

# Show sample of results
print("\nSample of results (first 10 with descriptions):")
sample = df[df['Description'] != ''].head(10)
if not sample.empty:
    for idx, row in sample.iterrows():
        item_id = row[id_column]
        desc = row['Description']
        print(f"  ID {item_id}: {desc[:60]}{'...' if len(desc) > 60 else ''}")
else:
    print("  No descriptions found")