import pandas as pd
import requests
import time
from datetime import datetime

print("="*60)
print("FormID Description Fetcher")
print("="*60)

# Load the Excel file
print("Loading Excel file...")
df = pd.read_excel(r'C:\Download\item_titles.xlsx')
print(f"Loaded {len(df)} items")

# Ensure FormID is string type
df['FormID'] = df['FormID'].astype(str)

# Add description column if it doesn't exist
if 'Description' not in df.columns:
    df['Description'] = ''

# Function to get description from API
def get_description(form_id):
    try:
        url = f'https://dentwizard.lgstore.com/lg/API/v1/products/get-description.php?form_id={form_id}'
        response = requests.get(url, timeout=5)
        if response.status_code == 200:
            data = response.json()
            if data.get('success'):
                return data['data'].get('description', '')
    except Exception as e:
        print(f"  Error for {form_id}: {str(e)}")
    return ''

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
        form_id = df.at[idx, 'FormID']
        desc = get_description(form_id)
        
        if desc:
            df.at[idx, 'Description'] = desc
            success_count += 1
            print(f"  [OK] {form_id}: {desc[:60]}{'...' if len(desc) > 60 else ''}")
        else:
            df.at[idx, 'Description'] = ''
            if idx < 10:  # Only show first few failures
                print(f"  [--] {form_id}: No description found")
    
    # Small delay between batches
    time.sleep(0.5)

# Save results
output_file = r'C:\Download\item_titles_with_descriptions.xlsx'
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
sample = df[df['Description'] != ''].head(10)[['FormID', 'Description']]
for idx, row in sample.iterrows():
    print(f"  {row['FormID']}: {row['Description'][:60]}{'...' if len(row['Description']) > 60 else ''}")