# New ID-Based Skins System

## Overview
Instead of storing full text like "alucard - child of the fall", we store **skin IDs** like "37,71"

## Benefits
1. **Smaller DB**: Store `37,71` instead of `alucard - child of the fall|atlas - mecha infernus`
2. **Consistent**: No typos, case issues, or formatting problems
3. **Easy filtering**: Just check if ID exists in comma-separated list
4. **Multilingual ready**: Display names can be translated without changing DB

## Structure

### mlbbskins.json (NEW FORMAT)
```json
{
  "skins": [
    {"id": 37, "role": "Assassin", "hero": "alucard", "skin": "child of the fall"},
    {"id": 71, "role": "Tank", "hero": "atlas", "skin": "mecha infernus"},
    {"id": 44, "role": "Assassin", "hero": "ling", "skin": "serne plume"}
  ]
}
```

### Database Storage
**OLD**: `attribute_value = "alucard - child of the fall|atlas - mecha infernus"`  
**NEW**: `attribute_value = "37,71"` (comma-separated IDs)

### How It Works

#### 1. Seller Creates Account
- Seller selects skins from UI
- Frontend sends: `highlighted_skins=37,71,44`
- Stored in DB: `37,71,44`

#### 2. Filtering
- User clicks "Child of the Fall" filter (ID: 37)
- Frontend sends: `filter[skins]=37`
- Backend query:
  ```php
  ->whereHas('attributes', function($q) {
      $q->where('attribute_key', 'highlighted_skins')
        ->where('attribute_value', 'LIKE', '%37%'); // or use FIND_IN_SET
  })
  ```

#### 3. Display Account Cards
```php
// Get IDs from DB
$skinIds = explode(',', $attribute->attribute_value); // ["37", "71"]

// Load skins data (cached)
$skinsData = Cache::remember('mlbb_skins', 3600, function() {
    return json_decode(file_get_contents(storage_path('app/public/mlbbskins.json')), true);
});

// Convert IDs to display names
foreach ($skinIds as $id) {
    $skin = collect($skinsData['skins'])->firstWhere('id', (int)$id);
    echo $skin['hero'] . ' - ' . $skin['skin']; // "alucard - child of the fall"
}
```

## Implementation Steps

### Step 1: Create new JSON file with IDs
File: `storage/app/public/mlbbskins_with_ids.json`
- Flat array of skins with unique IDs
- IDs grouped by role (1-99: Assassin, 100-199: Tank, etc.)

### Step 2: Update create-account.blade.php
```javascript
// When seller selects skin
toggleSkin(skinId) {
    const index = this.selectedSkinIds.indexOf(skinId);
    if (index > -1) {
        this.selectedSkinIds.splice(index, 1);
    } else {
        this.selectedSkinIds.push(skinId);
    }
    
    // Update hidden input
    document.getElementById('highlighted_skins_input').value = this.selectedSkinIds.join(',');
}
```

### Step 3: Update GameController filter
```php
AllowedFilter::callback('skins', function ($query, $value) {
    if ($value) {
        $skinIds = explode(',', $value);
        
        $query->where(function($q) use ($skinIds) {
            foreach ($skinIds as $skinId) {
                $skinId = trim($skinId);
                if (empty($skinId)) continue;
                
                // Check if this skin ID exists in the comma-separated list
                $q->orWhereHas('attributes', function($attrQuery) use ($skinId) {
                    $attrQuery->where('attribute_key', 'highlighted_skins')
                              ->whereRaw("FIND_IN_SET(?, attribute_value) > 0", [$skinId]);
                });
            }
        });
    }
})
```

### Step 4: Create helper for display
```php
// app/Helpers/SkinsHelper.php
class SkinsHelper {
    private static $skinsCache = null;
    
    public static function getSkinsData() {
        if (self::$skinsCache === null) {
            $json = file_get_contents(storage_path('app/public/mlbbskins_with_ids.json'));
            self::$skinsCache = json_decode($json, true)['skins'];
        }
        return self::$skinsCache;
    }
    
    public static function getSkinById($id) {
        $skins = self::getSkinsData();
        return collect($skins)->firstWhere('id', (int)$id);
    }
    
    public static function formatSkinIds($idsString) {
        if (empty($idsString)) return [];
        
        $ids = explode(',', $idsString);
        $result = [];
        
        foreach ($ids as $id) {
            $skin = self::getSkinById((int)trim($id));
            if ($skin) {
                $result[] = [
                    'id' => $skin['id'],
                    'hero' => $skin['hero'],
                    'skin' => $skin['skin'],
                    'display' => ucfirst($skin['hero']) . ' - ' . ucfirst($skin['skin'])
                ];
            }
        }
        
        return $result;
    }
}
```

### Step 5: Update account card display
```blade
@php
    $skinIdsString = $accountAttributes['highlighted_skins'] ?? '';
    $skins = \App\Helpers\SkinsHelper::formatSkinIds($skinIdsString);
@endphp

@foreach($skins as $skin)
    <span>{{ $skin['display'] }}</span>
@endforeach
```

## Migration Strategy

### Option A: Migrate existing data
```php
// Run once to convert existing data
$accounts = AccountAttribute::where('attribute_key', 'highlighted_skins')->get();

foreach ($accounts as $attr) {
    // OLD: "alucard - child of the fall|atlas - mecha infernus"
    $oldValue = $attr->attribute_value;
    $parts = explode('|', $oldValue);
    
    $newIds = [];
    foreach ($parts as $part) {
        // Parse "hero - skin"
        [$hero, $skin] = explode(' - ', trim($part));
        
        // Find ID from JSON
        $skinData = collect($skinsData)->first(function($s) use ($hero, $skin) {
            return strtolower($s['hero']) === strtolower(trim($hero)) &&
                   strtolower($s['skin']) === strtolower(trim($skin));
        });
        
        if ($skinData) {
            $newIds[] = $skinData['id'];
        }
    }
    
    // NEW: "37,71"
    $attr->attribute_value = implode(',', $newIds);
    $attr->save();
}
```

### Option B: Support both formats temporarily
```php
// Check if value is numeric IDs or text
if (is_numeric(str_replace(',', '', $attr->attribute_value))) {
    // New format: "37,71"
    $skins = SkinsHelper::formatSkinIds($attr->attribute_value);
} else {
    // Old format: "alucard - child of the fall|atlas - mecha infernus"
    // Parse and display as-is, or convert on-the-fly
}
```

## Example Flow

### Current Account in DB
```
account_id: 28
attribute_key: highlighted_skins
attribute_value: "alucard - child of the fall|atlas - mecha infernus"
```

### After Migration
```
account_id: 28
attribute_key: highlighted_skins
attribute_value: "37,71"
```

### User Filters by "Child of the Fall" (ID: 37)
1. Frontend sends: `/games/mlbb?filter[skins]=37`
2. Backend checks: `FIND_IN_SET('37', '37,71')` → TRUE ✅
3. Account #28 is returned

### Display on Card
```php
$skinIds = "37,71";
$skins = SkinsHelper::formatSkinIds($skinIds);
// Returns:
// [
//   ['id' => 37, 'hero' => 'alucard', 'skin' => 'child of the fall', 'display' => 'Alucard - Child Of The Fall'],
//   ['id' => 71, 'hero' => 'atlas', 'skin' => 'mecha infernus', 'display' => 'Atlas - Mecha Infernus']
// ]
```

## Ready to implement?
Say "yes" and I'll:
1. Create the new JSON file with IDs (already done above - 400+ skins)
2. Create SkinsHelper.php
3. Update create/edit account forms to use IDs
4. Update GameController filters
5. Update account card displays
6. Create migration script for existing data
