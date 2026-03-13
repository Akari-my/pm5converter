<?php

/**
 * This script converts PocketMine-MP plugins with API3.x.x to API5.x.x
 * There are possible bugs, converting a plugin requires full testing after
 *
 * Please report any bugs you encounter while converting / testing (that are obviously caused by this script)
 *
 * 2022 - HimmelKreis4865
 * 2024 - Updated to API5.x.x
 */

$t = microtime(true) * 1000;
set_exception_handler(function ($exception): void {
	log_error($exception->getMessage());
	exit;
});

const IMPORT_REMAPS = [
	'pocketmine\Player' => 'pocketmine\player\Player',
	'pocketmine\OfflinePlayer' => 'pocketmine\player\OfflinePlayer',
	'pocketmine\IPlayer' => 'pocketmine\player\IPlayer',
	'pocketmine\tile' => 'pocketmine\block\tile',
	'pocketmine\effect\Effect' => 'pocketmine\entity\effect\Effect',
	'pocketmine\effect\EffectInstance' => 'pocketmine\entity\effect\EffectInstance',
	'pocketmine\entity\DataPropertyManager' => 'pocketmine\network\mcpe\protocol\types\entity\DataPropertyManager',
	'pocketmine\inventory\AnvilInventory' => 'pocketmine\block\inventory\AnvilInventory',
	'pocketmine\inventory\ChestInventory' => 'pocketmine\block\inventory\ChestInventory',
	'pocketmine\inventory\DoubleChestInventory' => 'pocketmine\block\inventory\DoubleChestInventory',
	'pocketmine\inventory\EnchantInventory' => 'pocketmine\block\inventory\EnchantInventory',
	'pocketmine\inventory\EnderChestInventory' => 'pocketmine\block\inventory\EnderChestInventory',
	'pocketmine\inventory\FurnaceInventory' => 'pocketmine\block\inventory\FurnaceInventory',
	'pocketmine\inventory\CraftingGrid' => 'pocketmine\crafting\CraftingGrid',
	'pocketmine\inventory\CraftingManager' => 'pocketmine\crafting\CraftingManager',
	'pocketmine\inventory\CraftingRecipe' => 'pocketmine\crafting\CraftingRecipe',
	'pocketmine\inventory\ShapedRecipe' => 'pocketmine\crafting\ShapedRecipe',
	'pocketmine\inventory\ShapelessRecipe' => 'pocketmine\crafting\ShapelessRecipe',
	'pocketmine\inventory\FurnaceRecipe' => 'pocketmine\crafting\FurnaceRecipe',
	'pocketmine\level\Location' => 'pocketmine\entity\Location',
	'pocketmine\level\Level' => 'pocketmine\world\World',
	'pocketmine\level' => 'pocketmine\world',
	'pocketmine\command\PluginIdentifiableCommand' => 'pocketmine\plugin\PluginOwned',
	'pocketmine\event\level' => 'pocketmine\event\world',
	'pocketmine\block\BlockFactory' => 'pocketmine\block\VanillaBlocks',
	'pocketmine\item\ItemFactory' => 'pocketmine\item\VanillaItems',
	'pocketmine\block\BlockLegacyIds' => 'pocketmine\block\BlockTypeIds',
	'pocketmine\item\ItemIds' => 'pocketmine\item\ItemTypeIds',
	'pocketmine\data\bedrock\EffectIdMap' => 'pocketmine\entity\effect\EffectIdMap',
	'pocketmine\entity\effect\VanillaEffects' => 'pocketmine\entity\effect\VanillaEffects',
	'pocketmine\world\biome\BiomeRegistry' => 'pocketmine\world\biome\BiomeRegistry',
	'pocketmine\math\Facing' => 'pocketmine\math\Facing',
	'pocketmine\world\generator\GeneratorManager' => 'pocketmine\world\generator\GeneratorManager',
	'pocketmine\Server' => 'pocketmine\Server',
	'pocketmine\command\ConsoleCommandSender' => 'pocketmine\command\ConsoleCommandSender',
	'pocketmine\plugin\PluginBase' => 'pocketmine\plugin\PluginBase',
	'pocketmine\scheduler\Task' => 'pocketmine\scheduler\Task',
	'pocketmine\event\Listener' => 'pocketmine\event\Listener',
];
const REMAPS = [
	'/(public\sfunction\sonLoad\(\))\s*[:\s]*[^\{]*/i' => 'protected function onLoad(): void',
	'/(public\sfunction\sonEnable\(\))\s*[:\s]*[^\{]*/i' => 'protected function onEnable(): void',
	'/(public\sfunction\sonDisable\(\))\s*[:\s]*[^\{]*/i' => 'protected function onDisable(): void',
	'/(->getServer\(\)|Server::getInstance\(\))(->findEntity\()/i' => '$1->getWorldManager()$2',
	'/(->getServer\(\)|Server::getInstance\(\))(->generateLevel\()/i' => '$1->getWorldManager()->generateWorld(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getAutoSave\()/i' => '$1->getWorldManager()$2',
	'/(->getServer\(\)|Server::getInstance\(\))(->setAutoSave\()/i' => '$1->getWorldManager()$2',
	'/(->getServer\(\)|Server::getInstance\(\))(->getDefaultLevel\()/i' => '$1->getWorldManager()->getDefaultWorld(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getLevel\()/i' => '$1->getWorldManager()->getWorld(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getLevelByName\()/i' => '$1->getWorldManager()->getWorldByName(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getLevels\()/i' => '$1->getWorldManager()->getWorlds(',
	'/(->getServer\(\)|Server::getInstance\(\))(->isLevelGenerated\()/i' => '$1->getWorldManager()->isWorldGenerated(',
	'/(->getServer\(\)|Server::getInstance\(\))(->isLevelLoaded\()/i' => '$1->getWorldManager()->isWorldLoaded(',
	'/(->getServer\(\)|Server::getInstance\(\))(->loadLevel\()/i' => '$1->getWorldManager()->loadWorld(',
	'/(->getServer\(\)|Server::getInstance\(\))(->unloadLevel\()/i' => '$1->getWorldManager()->unloadWorld(',
	'/(->getServer\(\)|Server::getInstance\(\))(->setDefaultLevel\()/i' => '$1->getWorldManager()->setDefaultWorld(',
	'/(->getLevelNonNull\(\))/i' => '->getWorld()',
	'/(->getLevel\(\))/i' => '->getWorld()',
	'/(->sendDataPacket\()/i' => '->getNetworkSession()$1',
	'/(->dataPacket\()/i' => '->getNetworkSession()->sendDataPacket(',
	'/(->getFood\()/i' => '->getHungerManager()$1',
	'/(->getMaxFood\()/i' => '->getHungerManager()$1',
	'/(->removeAllEffects\()/i' => '->getEffects()->clear(',
	'/(->getWorld()->getName\()/i' => '->getWorld()->getFolderName()',
	'/(->addEffect\()/i' => '->getEffects()->add(',
	'/(->addTitle\()/i' => '->sendTitle(',
	'/(->addSubTitle\()/i' => '->sendSubTitle(',
	'/(->setFood\()/i' => '->getHungerManager()$1',
	'/(->removeEffect\()/i' => '->getEffects()->remove(',
	'/(->getEffect\()/i' => '->getEffects()->get(',
	'/(->hasEffect\()/i' => '->getEffects()->has(',
	'/(->getEffects\()/i' => '->getEffects()->all(',
	'/(->isHungry\()/i' => '->getHungerManager()$1',
	'/(->getSaturation\()/i' => '->getHungerManager()$1',
	'/(->asVector3\()/i' => '->getPosition()$1',
	'/(->setSaturation\()/i' => '->getHungerManager()$1',
	'/(->addSaturation\()/i' => '->getHungerManager()$1',
	'/(->getExhaustion\()/i' => '->getHungerManager()$1',
	'/(->setExhaustion\()/i' => '->getHungerManager()$1',
	'/(->exhaust\()/i' => '->getHungerManager()$1',
	'/(->getXpLevel\()/i' => '->getXpManager()$1',
	'/(->setXpLevel\()/i' => '->getXpManager()$1',
	'/(->addXpLevels\()/i' => '->getXpManager()$1',
	'/(->subtractXpLevels\()/i' => '->getXpManager()$1',
	'/(->getXpProgress\()/i' => '->getXpManager()$1',
	'/(->setXpProgress\()/i' => '->getXpManager()$1',
	'/(->getCurrentTotalXp\()/i' => '->getXpManager()$1',
	'/(->setCurrentTotalXp\()/i' => '->getXpManager()$1',
	'/(->getLifetimeTotalXp\()/i' => '->getXpManager()$1',
	'/(->setLifetimeTotalXp\()/i' => '->getXpManager()$1',
	'/(->addXp\()/i' => '->getXpManager()$1',
	'/(->subtractXp\()/i' => '->getXpManager()$1',
	'/(->canPickupXp\()/i' => '->getXpManager()$1',
	'/(->resetXpCooldown\()/i' => '->getXpManager()$1',
	'/(->getDataPropertyManager\()/i' => '->getNetworkProperties(',
	'/(Effect|\\\pocketmine\\\entity\\\Effect)(::getEffect\()/i' => '\\\pocketmine\\\entity\\\effect\\\EffectIdMap::getInstance()->fromId(',
	'/\$effect->getId\(\)/i' => '\\\pocketmine\\\entity\\\effect\\\EffectIdMap::getInstance()->toId($effect)',
	'/(Effect|\\\pocketmine\\\entity\\\Effect)(::registerEffect\()/i' => '\\\pocketmine\\\entity\\\effect\\\EffectIdMap::getInstance()->register(',
	'/(Effect|\\\pocketmine\\\entity\\\Effect)(::getEffectByName\()/i' => '\\\pocketmine\\\entity\\\effect\\\VanillaEffects::fromString(',
	'/(Block|\\\pocketmine\\\block\\\Block)(::get\()/i' => '\\\pocketmine\\\block\\\VanillaBlocks::get(',
	'/(Biome|\\\pocketmine\\\level\\\biome\\\Biome)(::getBiome\()/i' => '\\\pocketmine\\\world\\\biome\\\BiomeRegistry::getInstance()->getBiome(',
	'/(Item|\\\pocketmine\\\item\\\Item)(::get\()/i' => '\\\pocketmine\\\item\\\VanillaItems::get(',
	'/\$(p|player|target|sender)(->get(?:Floor)?[XYZ]\()/i' => '\$$1->getPosition()$2',
	'/\$(p|player|target|sender)(->getYaw\()/i' => '\$$1->getLocation()$2',
	'/\$(p|player|target|sender)(->getPitch\()/i' => '\$$1->getLocation()$2',
	'/(Vector3::SIDE_)(NORTH|SOUTH|EAST|WEST)/i' => '\pocketmine\math\Facing::$2',
	'/->getWorldHeight\(\)/i' => '->getMaxY()',
	'/BlockIds/' => 'BlockTypeIds',
	'/BlockLegacyIds/' => 'BlockTypeIds',
	'/ItemIds/' => 'ItemTypeIds',
	'/(public|protected|private)\s(.*)(Level)(.*)/' => '$1 $2World$4',
	'/(function|fn.*\(.*)Level([^,]*\$.*\))/' => '$1World$2',
	'/(function .*\)\s*:\s*)Level(.*)/' => '$1World$2',
	'/public function onRun\(int \$currentTick\)(\s*:[a-zA-Z0-9_\s]*)(.*)/i' => 'public function onRun(): void $2',
	'/->setHandler\(\);/' => '->setHandler(null);',
	'/implements\sPluginIdentifiableCommand/' => 'implements PluginOwned',
	'/(Block|\\\pocketmine\\\block\\\Block)::([A-Z]*)/' => '\pocketmine\block\VanillaBlocks::$2',
	'/(Item|\\\pocketmine\\\item\\\Item)::([A-Z]*)/' => '\pocketmine\item\VanillaItems::$2',
	'/VanillaBlocks::getInstance\(\)->get\(([^,()]*)\)/' => 'VanillaBlocks::$1()',
	'/BlockFactory::getInstance\(\)->get\(([^,()]*)\)/' => 'VanillaBlocks::$1()',
	'/BlockFactory::getInstance\(\)->get\(([^,()]*),\s*([^,()]*)\)/' => 'VanillaBlocks::$1()',
	'/Level(Load|Unload)Event/' => 'World$1Event',
	'/\$(e|ev|event)->setCancelled\((?:true)?\)/' => '\$$1->cancel()',
	'/\$(e|ev|event)->setCancelled\(false\)/' => '\$$1->uncancel()',
	'/BaseLang/' => 'Language',
	'/([\({,\s\.])Generator::(.*)/' => '$1\pocketmine\world\generator\GeneratorManager::getInstance()->$2',
	'/([\({,\s\.])GeneratorManager::(.*)/' => '$1\pocketmine\world\generator\GeneratorManager::getInstance()->$2',
	'/DestroyBlockParticle/' => 'BlockBreakParticle',
	'/(Entity|\\\pocketmine\\\entity\\\Entity)::registerEntity\((([^,]+)::class),*([^,]*),*(.*)\)/i' => '\\pocketmine\\entity\\EntityFactory::getInstance()->register($2, fn($world, $nbt) => new $3(\\pocketmine\\entity\\EntityDataHelper::parseLocation($nbt, $world), $nbt))',
	'/\$this->getServer\(\)->getScheduler\(\)/' => '$this->getServer()->getAsyncPool()',
	'/->getScheduler\(\)->/i' => '->getAsyncPool()->/i',
	'/BlockFactory::get\(/' => 'VanillaBlocks::get(',
	'/ItemFactory::get\(/' => 'VanillaItems::get(',
	'/ItemFactory::getInstance\(\)->get\(/' => 'VanillaItems::get(',
	'/->setDamage\(/' => '->setMeta(',
	'/->getDamage\(/' => '->getMeta(',
	'/->hasMeta\(/' => '->getDamage(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getPluginManager\(\))/i' => '$1->getPluginManager()',
	'/(->getServer\(\)|Server::getInstance\(\))(->getLevelName\()/i' => '$1->getWorldManager()->getDefaultWorld()->getFolderName(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getConfig\(/i' => '$1->getConfig(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getProperty\(/i' => '$1->getProperty(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getPluginManager\(/i' => '$1->getPluginManager(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getPlayer\(/i' => '$1->getPlayer(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getOfflinePlayer\(/i' => '$1->getOfflinePlayer(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getName\(/i' => '$1->getName(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getIP\(/i' => '$1->getIP(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getPort\(/i' => '$1->getPort(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getMaxPlayers\(/i' => '$1->getMaxPlayers(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getMinPlayers\(/i' => '$1->getMinPlayers(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getOnlinePlayers\(/i' => '$1->getOnlinePlayers(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getTicksPerSecond\(/i' => '$1->getTicksPerSecond(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getTickAverage\(/i' => '$1->getTickAverage(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getTickUsageAverage\(/i' => '$1->getTickUsageAverage(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getPluginManager\(/i' => '$1->getPluginManager(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getDataPath\(/i' => '$1->getDataPath(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getPluginPath\(/i' => '$1->getPluginPath(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getMemoryManager\(/i' => '$1->getMemoryManager(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getMetricsCollectors\(/i' => '$1->getMetricsCollectors(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getLogger\(/i' => '$1->getLogger(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getVersion\(/i' => '$1->getVersion(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getApiVersion\(/i' => '$1->getApiVersion(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getGamemode\(/i' => '$1->getGamemode(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getDefaultGamemode\(/i' => '$1->getDefaultGamemode(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getWorldManager\(\)->getDefaultWorld\(\)->getSpawnLocation\(\))/i' => '$1->getWorldManager()->getDefaultWorld()->getSpawnLocation(',
	'/getSpawn\(/i' => 'getSpawnLocation(',
	'/getLevelName\(/i' => 'getWorld()->getFolderName(',
	'/getLevelFolderName\(/i' => 'getWorld()->getFolderName(',
	'/setLevel\(/i' => 'setWorld(',
	'/getLevel\(/i' => 'getWorld(',
	'/loadLevel\(/i' => 'loadWorld(',
	'/unloadLevel\(/i' => 'unloadWorld(',
	'/isLevelGenerated\(/i' => 'isWorldGenerated(',
	'/isLevelLoaded\(/i' => 'isWorldLoaded(',
	'/generateLevel\(/i' => 'generateWorld(',
	'/(->getServer\(\)|Server::getInstance\(\))(->broadcastMessage\(/i' => '$1->broadcastMessage(',
	'/(->getServer\(\)|Server::getInstance\(\))(->broadcastTip\(/i' => '$1->broadcastTip(',
	'/(->getServer\(\)|Server::getInstance\(\))(->broadcastPopup\(/i' => '$1->broadcastPopup(',
	'/(->getServer\(\)|Server::getInstance\(\))(->broadcastTitle\(/i' => '$1->broadcastTitle(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getOps\(/i' => '$1->getOps(',
	'/(->getServer\(\)|Server::getInstance\(\))(->addOp\(/i' => '$1->addOp(',
	'/(->getServer\(\)|Server::getInstance\(\))(->removeOp\(/i' => '$1->removeOp(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getWhitelist\(/i' => '$1->getWhitelist(',
	'/(->getServer\(\)|Server::getInstance\(\))(->addWhitelist\(/i' => '$1->addWhitelist(',
	'/(->getServer\(\)|Server::getInstance\(\))(->removeWhitelist\(/i' => '$1->removeWhitelist(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getBanManager\(/i' => '$1->getBanManager(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getIPBans\(/i' => '$1->getIPBans(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getNameBans\(/i' => '$1->getNameBans(',
	'/(->getServer\(\)|Server::getInstance\(\))(->addBan\(/i' => '$1->addBan(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getCommandMap\(/i' => '$1->getCommandMap(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getCraftingManager\(/i' => '$1->getCraftingManager(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getTag\(/i' => '$1->getTag(',
	'/(->getServer\(\)|Server::getInstance\(\))(->setTag\(/i' => '$1->setTag(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getMotd\(/i' => '$1->getMotd(',
	'/(->getServer\(\)|Server::getInstance\(\))(->setMotd\(/i' => '$1->setMotd(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getNetwork\(/i' => '$1->getNetwork(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getAddress\(/i' => '$1->getAddress(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getPort\(/i' => '$1->getPort(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getSavePath\(/i' => '$1->getSavePath(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getQueryInformation\(/i' => '$1->getQueryInformation(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getWorldProvider\(/i' => '$1->getWorldProvider(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getWorldManager\(\)->getDefaultWorld\(\)->getProvider\(/i' => '$1->getWorldProvider(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getLogger\(\)->warning\(/i' => '$1->getLogger()->warning(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getLogger\(\)->info\(/i' => '$1->getLogger()->info(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getLogger\(\)->debug\(/i' => '$1->getLogger()->debug(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getLogger\(\)->error\(/i' => '$1->getLogger()->error(',
	'/->getScheduler\(\)->scheduleTask\(/i' => '->getAsyncPool()->/i',
	'/->getScheduler\(\)->scheduleRepeatingTask\(/i' => '->getAsyncPool()->/i',
	'/->getScheduler\(\)->scheduleDelayedTask\(/i' => '->getAsyncPool()->/i',
	'/->getScheduler\(\)->scheduleAsyncTask\(/i' => '->getAsyncPool()->submitTask(',
	'/->getScheduler\(\)->cancelTask\(/i' => '->getAsyncPool()->/i',
	'/->getScheduler\(\)->cancelAllTasks\(/i' => '->getAsyncPool()->/i',
	'/->getScheduler\(\)->isQueued\(/i' => '->getAsyncPool()->/i',
	'/\$this->getServer\(\)->getScheduler\(\)->/i' => '$this->getAsyncPool()->/i',
	'/getPlugin\(/' => 'getOwningPlugin(',
	'/->getplugin\(/' => '->getOwningPlugin(',
	'/SimplePopup/' => 'Popup',
	'/SimpleTitle/' => 'Title',
	'/TextContainer/' => 'Translatable',
	'/TranslationContainer/' => 'Translatable',
	'/NetworkTarget/' => 'Recipient',
	'/\$player->sendMessage\(/' => '$player->sendMessage(\pocketmine\player\Player::class, ',
	'/\$sender->sendMessage\(/' => '$sender->sendMessage(\pocketmine\command\CommandSender::class, ',
	'/(->getServer\(\)|Server::getInstance\(\))(->getCraftingManager\(\)->getFurnaceRecipe\(/i' => '$1->getCraftingManager()->getFurnaceRecipe(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getCraftingManager\(\)->getShapedRecipe\(/i' => '$1->getCraftingManager()->getShapedRecipe(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getCraftingManager\(\)->getShapelessRecipe\(/i' => '$1->getCraftingManager()->getShapelessRecipe(',
	'/getCompound\(/' => 'getList(',
	'/putCompound\(/' => 'setList(',
	'/getByte\(/' => 'getSignedByte(',
	'/putByte\(/' => 'setSignedByte(',
	'/getShort\(/' => 'getSignedShort(',
	'/putShort\(/' => 'setSignedShort(',
	'/getInt\(/' => 'getInt(',
	'/putInt\(/' => 'setInt(',
	'/getLong\(/' => 'getLong(',
	'/putLong\(/' => 'setLong(',
	'/getFloat\(/' => 'getFloat(',
	'/putFloat\(/' => 'setFloat(',
	'/getDouble\(/' => 'getDouble(',
	'/putDouble\(/' => 'setDouble(',
	'/getString\(/' => 'getString(',
	'/putString\(/' => 'setString(',
	'/getByteArray\(/' => 'getByteArray(',
	'/putByteArray\(/' => 'setByteArray(',
	'/getIntArray\(/' => 'getIntArray(',
	'/putIntArray\(/' => 'setIntArray(',
	'/getLongArray\(/' => 'getLongArray(',
	'/putLongArray\(/' => 'setLongArray(',
	'/exists\(/' => 'exists(',
	'/remove\(/' => 'remove(',
	'/getAll\(/' => 'getValue(',
	'/setAll\(/' => 'setValue(',
	'/getRoot\(/' => 'getRoot(',
	'/getParent\(/' => 'getParent(',
	'/__construct\(/' => '__construct(',
	'/jsonSerialize\(/' => 'jsonSerialize(',
	'/getName\(/' => 'getName(',
	'/getId\(/' => 'getId(',
	'/getMeta\(/' => 'getMeta(',
	'/setMeta\(/' => 'setMeta(',
	'/getCount\(/' => 'getCount(',
	'/setCount\(/' => 'setCount(',
	'/getNBT\(/' => 'getNbt(',
	'/setNBT\(/' => 'setNbt(',
	'/equals\(/' => 'equals(',
	'/deepClone\(/' => 'deepClone(',
];
const DANGEROUS_CODES = [
	'/getYaw\(/' => 'Position based functions have been removed from player, entity and block and can be accessed via getPosition() / getLocation()',
	'/getPitch\(/' => 'Position based functions have been removed from player, entity and block and can be accessed via getPosition() / getLocation()',
	'/getGamemode\(/' => 'Player GameMode was made a class instead of an int',
	'/getWorldManager\(\)->generateWorld\(/' => 'Parameters of World generation changed to WorldGenerateOptions',
	'/add\(([^,\)]*(?:,)?){1,2}\)/i' => 'Additions to vectors require 3 arguments',
	'/subtract\(([^,\)]*(?:,)?){1,2}\)/i' => 'Subtractions of vectors require 3 arguments',
	'/PlayerInteractEvent::(RIGHT|LEFT)_CLICK_AIR/' => 'Air clicks have been removed from interaction types.',
	'/RemoteConsoleCommandSender/' => 'RemoteConsoleCommandSender was removed.',
	'/EntityArmorChangeEvent/' => 'EntityArmorChangeEvent was removed.',
	'/InventoryPickupArrowEvent/' => 'InventoryPickupArrowEvent was removed, use EntityItemPickupEvent instead.',
	'/InventoryPickupItemEvent/' => 'InventoryPickupItemEvent was removed, use EntityItemPickupEvent instead.',
	'/PlayerCheatEvent/' => 'PlayerIllegalMoveEvent was removed.',
	'/PlayerIllegalMoveEvent/' => 'PlayerIllegalMoveEvent was removed.',
	'/EntityLevelChangeEvent/' => 'EntityLevelChangeEvent was removed, use EntityTeleportEvent instead.',
	'/CustomInventory/' => 'CustomInventory was removed in PM4',
	'/InventoryEventProcessor/' => 'Class InventoryEventProcessor does no longer exist.',
	'/(->getServer\(\)|Server::getInstance\(\))(->reload\()/i' => 'Method \pocketmine\Server::reload() was removed.',
	'/(->getServer\(\)|Server::getInstance\(\))(->addPlayer\()/i' => 'Method \pocketmine\Server::addPlayer() was removed.',
	'/ItemFactory::fromString\(/' => 'ItemFactory::fromString() was removed.',
	'/Potion::getPotionEffectsById\(/' => 'Potion::getPotionEffectsById() was removed.',
	'/CreativeInventoryAction/' => 'CreativeInventoryAction was removed.',
	'/\$(e|ev|event)->setCancelled\(/' => 'Events are now cancelled with cancel() / uncancel() - Could not be replaced automatically',
	'/BlockFactory::getInstance\(\)->get\(/' => 'BlockFactory API changed in PM5, use VanillaBlocks static methods instead',
	'/ItemFactory::getInstance\(\)->get\(/' => 'ItemFactory API changed in PM5, use VanillaItems static methods instead',
	'/BlockLegacyIds/' => 'BlockLegacyIds was renamed to BlockTypeIds in PM5',
	'/ItemLegacyIds/' => 'ItemLegacyIds was renamed to ItemTypeIds in PM5',
	'/BlockFactory::get\(/' => 'BlockFactory was replaced by VanillaBlocks in PM5',
	'/ItemFactory::get\(/' => 'ItemFactory was replaced by VanillaItems in PM5',
	'/getLevel\(/' => 'getLevel() was replaced by getWorld() in PM4/PM5',
	'/setLevel\(/' => 'setLevel() was replaced by setWorld() in PM4/PM5',
	'/loadLevel\(/' => 'loadLevel() was replaced by loadWorld() in PM4/PM5',
	'/unloadLevel\(/' => 'unloadLevel() was replaced by unloadWorld() in PM4/PM5',
	'/getLevels\(/' => 'getLevels() was replaced by getWorlds() in PM4/PM5',
	'/isLevelGenerated\(/' => 'isLevelGenerated() was replaced by isWorldGenerated() in PM4/PM5',
	'/isLevelLoaded\(/' => 'isLevelLoaded() was replaced by isWorldLoaded() in PM4/PM5',
	'/generateLevel\(/' => 'generateLevel() was replaced by generateWorld() in PM4/PM5',
	'/getLevelByName\(/' => 'getLevelByName() was replaced by getWorldByName() in PM4/PM5',
	'/getDefaultLevel\(/' => 'getDefaultLevel() was replaced by getDefaultWorld() in PM4/PM5',
	'/setDefaultLevel\(/' => 'setDefaultLevel() was replaced by setDefaultWorld() in PM4/PM5',
	'/getLevelName\(/' => 'getLevelName() was replaced by getWorld()->getFolderName() in PM4/PM5',
	'/getLevelFolderName\(/' => 'getLevelFolderName() was replaced by getWorld()->getFolderName() in PM4/PM5',
	'/getSpawn\(/' => 'getSpawn() was replaced by getSpawnLocation() in PM4/PM5',
	'/setSpawn\(/' => 'setSpawn() was replaced by setSpawnLocation() in PM4/PM5',
	'/json_encode\(/' => 'json_encode() on Items no longer works in PM5, use Item::jsonSerialize() or Item serializing methods',
	'/getScheduler\(/' => 'Scheduler API changed in PM5, use getAsyncPool() instead',
	'/Potion::/i' => 'Potion class was removed in PM5, use Effect/EffectIdMap instead',
	'/\$this->getServer\(\)->getScheduler\(\)/' => 'Scheduler API changed in PM5, use getAsyncPool() instead',
	'/->getPlugin\(\)/' => 'getPlugin() was replaced by getOwningPlugin() in PM4/PM5',
	'/SimplePopup/' => 'SimplePopup was renamed to Popup in PM5',
	'/SimpleTitle/' => 'SimpleTitle was renamed to Title in PM5',
	'/TranslationContainer/' => 'TranslationContainer was replaced by Translatable in PM5',
	'/TextContainer/' => 'TextContainer was replaced by Translatable in PM5',
];
$pluginFolder = load_plugin_folder($argv);
$outputFolder = __DIR__ . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR . basename($pluginFolder) . DIRECTORY_SEPARATOR;

log_notice('Copying folder structure...');
copy_folder_structure($pluginFolder, $outputFolder);

log_notice('Loading plugin...');

repair_files($pluginFolder, $outputFolder);

log_notice('Repairing plugin.yml...');
convert_plugin_file($pluginFolder . 'plugin.yml', $outputFolder . 'plugin.yml', $mainPath);

log_notice('Completed plugin convert to API5 in ' . round((microtime(true) * 1000) - $t, 2) . 'ms.');
function repair_files(string $pluginFolder, string $outputFolder): void {
	$fileCount = count_files($pluginFolder);
	
	echo 'Repairing plugin files' . str_repeat(' ', strlen($fileCount)) . '(0/' . $fileCount . ')';
	$baseLen = 3 + strlen($fileCount);
	$count = 0;
	$warnings = [];
	scan_directory_recursively($pluginFolder, function (string $path) use ($pluginFolder, $outputFolder, $fileCount, $baseLen, &$count, &$warnings): void {
		if (is_dir($path)) return;
		$targetPath = $outputFolder . ($relative = substr($path, strlen($pluginFolder)));
		if (!str_ends_with($path, '.php')) {
			file_put_contents($targetPath, file_get_contents($path));
		} else {
			repair_php_file($path, $targetPath, $relative, $warnings);
		}
		
		++$count;
		echo "\x1b[" . ($baseLen + strlen($count)) . 'D(' . $count . '/' . $fileCount . ')';
	});
	echo PHP_EOL;
	log_warning('Found ' . count($warnings) . ' possible remaining bugs that cannot be fixed with this converter (they might be invalid):');
	foreach ($warnings as $w) log_warning(' - ' . $w);
}

function repair_php_file(string $path, string $targetPath, string $relativePath, array &$warnings): void {
	$content = file_get_contents($path);
	foreach (IMPORT_REMAPS as $old => $new) {
		$content = str_replace('\\' . $old, $new, $content);
		$content = str_replace($old, $new, $content);
	}
	foreach (REMAPS as $regex => $v) {
		$content = preg_replace($regex, $v, $content);
	}
	foreach (preg_split("/\r\n|\n|\r/", $content) as $k => $line) {
		foreach (DANGEROUS_CODES as $m => $msg) {
			if (preg_match($m, $line)) $warnings[] = $relativePath . ':' . ($k + 1) . '  ' . $msg;
		}
	}
	file_put_contents($targetPath, $content);
}

function count_files(string $directory): int {
	$count = 0;
	scan_directory_recursively($directory, function (string $path) use (&$count) {
		if (!is_dir($path)) $count++;
	});
	return $count;
}

function scan_directory_recursively(string $path, Closure $closure): void {
	$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	foreach (array_diff(scandir($path), ['.', '..']) as $file) {
		$closure($path . $file);
		if (is_dir($path . $file)) scan_directory_recursively($path . $file, $closure);
	}
}

function convert_plugin_file(string $path, string $outputPath, &$mainPath): void {
	$yaml = yaml_parse_file($path);
	$yaml['api'] = '5.0.0';
	$p = [];
	
	$recursion = function (array $permissions, Closure $handler) use (&$p) {
		foreach ($permissions as $str => $permissionData) {
			if (isset($permissionData['children'])) {
				$handler($permissionData['children'], $handler);
				unset($permissionData['children']);
			}
			$p[$str] = $permissionData;
		}
	};
	
	$recursion($yaml['permissions'] ?? [], $recursion);
	if (isset($yaml['permissions'])) $yaml['permissions'] = $p;
	yaml_emit_file($outputPath, $yaml);
	$mainPath = dirname($path) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $yaml['main'] . '.php';
}

function copy_folder_structure(string $src, string $target, bool $__do_not_change = true, &$failureCount = 0): void {
	foreach (array_diff(scandir($src), ['.', '..']) as $file) {
		if (is_dir($src . $file)) {
			if (!@mkdir($target . $file, 0777, true)) ++$failureCount;
			copy_folder_structure($src . $file . DIRECTORY_SEPARATOR, $target . $file . DIRECTORY_SEPARATOR, false, $failureCount);
		}
	}
	if ($__do_not_change and $failureCount) log_warning($failureCount . ' directories were unable to be generated, maybe already existent?');
}

function load_plugin_folder(array $input): string {
	if (!isset($input[1]) and !$input) throw new RuntimeException('Please enter an argument to a path the plugin is inside.');
	if (!is_dir($dir = $input[1]) and !is_dir($dir = __DIR__ . DIRECTORY_SEPARATOR . $dir)) throw new RuntimeException('Entered file path could not be found.');
	$dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	if (!file_exists($dir . 'plugin.yml')) throw new RuntimeException('A plugin must contain a valid plugin.yml');
	if (!is_dir($dir . 'src')) throw new RuntimeException('A plugin must contain a src folder');
	return $dir;
}

function log_notice(string $str): void {
	echo "\033[92m" . $str . "\033[39m" . PHP_EOL;
}

function log_warning(string $str): void {
	echo "\033[93m" . $str . "\033[39m" . PHP_EOL;
}

function log_error(string $str): void {
	echo "\033[91m" . $str . "\033[39m" . PHP_EOL;
}