# PM3 to PM5 Converter

A PHP script to convert PocketMine-MP plugins from API 3.x.x to API 5.x.x.

## ⚠️ Important Warnings

- **This converter is not perfect.** Many breaking changes between PM3 and PM5 cannot be automatically handled.
- **Full testing is required** after conversion. The converted plugin will likely have errors that need manual fixing.
- **You should have knowledge of PocketMine-MP API 5.x** before using this tool. Understanding the changes between versions is essential to fix remaining issues.
- **Backup your plugin** before running the converter.

## Requirements

- PHP 8.0+
- PHP YAML extension (`yaml`)

```

## Usage

Run the converter from the command line, passing the path to the plugin folder:

```bash
php pm5converter.php /path/to/your/plugin
```

The converted plugin will be saved in:

```
output/[plugin-name]/
```

### Example

```bash
php pm5converter.php /home/user/plugins/MyAwesomePlugin
```

## What the Converter Does

### 1. Namespace Remapping
Automatically updates old PM3 namespaces to PM5 equivalents:

| PM3 | PM5 |
|-----|-----|
| `pocketmine\level` | `pocketmine\world` |
| `pocketmine\tile` | `pocketmine\block\tile` |
| `pocketmine\block\BlockFactory` | `pocketmine\block\VanillaBlocks` |
| `pocketmine\item\ItemFactory` | `pocketmine\item\VanillaItems` |
| `pocketmine\block\BlockLegacyIds` | `pocketmine\block\BlockTypeIds` |

### 2. Method Renaming
Converts deprecated/renamed methods:

- `getLevel()` → `getWorld()`
- `setLevel()` → `setWorld()`
- `loadLevel()` → `loadWorld()`
- `unloadLevel()` → `unloadWorld()`
- `getLevelName()` → `getWorld()->getFolderName()`
- `getSpawn()` → `getSpawnLocation()`
- `->getScheduler()` → `->getAsyncPool()`
- `->getPlugin()` → `->getOwningPlugin()`

### 3. API Changes
Handles various API changes:

- **Events**: `setCancelled(true)` → `cancel()`
- **Player hunger**: `getFood()` → `getHungerManager()->getFood()`
- **Player XP**: `getXpLevel()` → `getXpManager()->getXpLevel()`
- **Effects**: `addEffect()` → `getEffects()->add()`
- **Block factory**: `BlockFactory::getInstance()->get($id, $meta)` → `VanillaBlocks::BLOCK_NAME()`
- **Item factory**: `ItemFactory::getInstance()->get($id, $meta)` → `VanillaItems::ITEM_NAME()`

### 4. plugin.yml Update
Automatically updates the `api` version in `plugin.yml` to `5.0.0`.

## Known Limitations

The converter cannot automatically handle the following PM5 changes:

### Major Breaking Changes

1. **Block State System**
   - PM5 uses a completely new block state system
   - Legacy block IDs and metadata are removed
   - `BlockTypeIds` replaces `BlockLegacyIds`

2. **Item System**
   - `ItemFactory` is completely removed
   - Use `VanillaItems` static methods instead
   - Durability now uses NBT `Damage` tag instead of metadata
   - `json_encode()` on items no longer works

3. **Potion Class**
   - The `Potion` class was removed
   - Use `Effect` and `EffectIdMap` instead

4. **Scheduler Changes**
   - Task scheduler API changed significantly
   - Use `AsyncPool` methods

5. **Inventory System**
   - `CustomInventory` was removed in PM4
   - Inventory events changed

### Removed Events/Classes
These are no longer available and need manual replacement:
- `EntityArmorChangeEvent`
- `InventoryPickupArrowEvent`
- `InventoryPickupItemEvent` → Use `EntityItemPickupEvent`
- `PlayerIllegalMoveEvent`
- `EntityLevelChangeEvent` → Use `EntityTeleportEvent`
- `RemoteConsoleCommandSender`

## After Conversion

1. **Review all warnings** - The converter prints warnings about code that couldn't be automatically converted

2. **Fix remaining issues** - Common fixes needed:
   - Update block/item creation to use new syntax
   - Fix event handlers
   - Update inventory code
   - Fix NBT handling

3. **Test thoroughly** - Test every feature of your plugin

## Example: Block Conversion

### Before (PM3):
```php
$block = BlockFactory::getInstance()->get(1, 0);
$item = ItemFactory::getInstance()->get(257, 0, 1);
```

### After (PM5):
```php
$block = VanillaBlocks::STONE();
$item = VanillaItems::IRON_PICKAXE()->setCount(1);
```

## Troubleshooting

### "yaml_parse_file undefined"
Install the PHP YAML extension:
```bash
# Linux
sudo apt install php-yaml

# macOS
brew install php-yaml
```

### Converted plugin doesn't work
This is expected. Manual fixing is required. Common issues:
- Check method names changed in PM5
- Verify block/item IDs are now using `VanillaBlocks`/`VanillaItems`
- Update event listener signatures

## Contributing

Feel free to submit pull requests to improve the converter. The conversion logic is defined in the `REMAPS` and `IMPORT_REMAPS` constants.

## License

MIT License

## Credits

- Original PM3 to PM4 converter by HimmelKreis4865
- Updated to PM5 by Mellooh
