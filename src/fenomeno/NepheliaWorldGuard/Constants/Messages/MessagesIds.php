<?php

namespace fenomeno\NepheliaWorldGuard\Constants\Messages;

final class MessagesIds
{
    // =========================================================================
    // LEGACY CONSTANTS
    // =========================================================================
    public const ALREADY_CREATING_REGION        = "region.create.already-creating";
    public const REGION_ALREADY_EXISTS          = "region.create.already-exists";
    public const REGION_INVALID_NAME            = "region.create.invalid-name";
    public const START_CREATING_EXTENDED_REGION = "region.create.start-extended";
    public const START_CREATING_REGION          = "region.create.start";
    public const REGION_CREATION_POS_1_SET      = "region.create.pos1-set";
    public const REGION_CREATION_POS_2_SET      = "region.create.pos2-set";
    public const REGION_CREATION_SUCCESS_GLOBAL = "region.create.global-success";
    public const REGION_CREATION_SUCCESS        = "region.create.success";
    public const REGION_NOT_FOUND               = "general.region-not-found";

    // =========================================================================
    // GENERAL
    // =========================================================================
    public const GENERAL_PLAYER_NOT_FOUND       = "general.player-not-found";
    public const GENERAL_INVALID_ARGUMENTS      = "general.invalid-arguments";
    public const GENERAL_RELOAD_SUCCESS         = "general.reload-success";
    public const GENERAL_RELOAD_FAILED          = "general.reload-failed";
    public const GENERAL_REGION_NOT_FOUND       = "general.region-not-found";

    // =========================================================================
    // REGION - CREATE
    // =========================================================================
    public const REGION_CREATE_START            = "region.create.start";
    public const REGION_CREATE_START_EXTENDED   = "region.create.start-extended";
    public const REGION_CREATE_POS_1_SET        = "region.create.pos1-set";
    public const REGION_CREATE_POS_2_SET        = "region.create.pos2-set";
    public const REGION_CREATE_SUCCESS          = "region.create.success";
    public const REGION_CREATE_ALREADY_EXISTS   = "region.create.already-exists";
    public const REGION_CREATE_INVALID_NAME     = "region.create.invalid-name";
    public const REGION_CREATE_GLOBAL_SUCCESS   = "region.create.global-success";
    public const REGION_CREATE_GLOBAL_EXISTS    = "region.create.global-exists";
    public const REGION_CREATE_ALREADY_CREATING = "region.create.already-creating";

    // =========================================================================
    // REGION - DELETE
    // =========================================================================
    public const REGION_DELETE_SUCCESS          = "region.delete.success";
    public const REGION_DELETE_NOT_FOUND        = "region.delete.not-found";
    public const REGION_DELETE_CONFIRM          = "region.delete.confirm";

    // =========================================================================
    // REGION - REDEFINE
    // =========================================================================
    public const REGION_REDEFINE_START              = "region.redefine.start";
    public const REGION_REDEFINE_SUCCESS            = "region.redefine.success";
    public const REGION_REDEFINE_ALREADY_REDEFINING = "region.redefine.already-redefining";
    public const REGION_REDEFINE_GLOBAL_NOT_ALLOWED = "region.redefine.global-not-allowed";
    public const REGION_REDEFINE_START_EXTENDED     = "region.redefine.start-extended";
    public const REGION_REDEFINE_FAILED             = "region.redefine.failed";
    public const REGION_REDEFINE_POS_1_SET          = "region.redefine.pos1-set";
    public const REGION_REDEFINE_POS_2_SET          = "region.redefine.pos2-set";
    public const REGION_REDEFINE_CANCELLED          = "region.redefine.cancelled";

    // =========================================================================
    // REGION - INFO
    // =========================================================================
    public const REGION_INFO_NOT_IN_REGION      = "region.info.not-in-region";
    public const REGION_INFO_HEADER             = "region.info.header";
    public const REGION_INFO_WORLD              = "region.info.world";
    public const REGION_INFO_PRIORITY           = "region.info.priority";
    public const REGION_INFO_POSITIONS          = "region.info.positions";

    // =========================================================================
    // REGION - LIST
    // =========================================================================
    public const REGION_LIST_HEADER             = "region.list.header";
    public const REGION_LIST_ENTRY              = "region.list.entry";
    public const REGION_LIST_EMPTY              = "region.list.empty";
    public const REGION_LIST_FOOTER             = "region.list.footer";

    // =========================================================================
    // REGION - PLAYER
    // =========================================================================
    public const REGION_PLAYER_IN_REGION        = "region.player.in-region";
    public const REGION_PLAYER_NOT_IN_REGION    = "region.player.not-in-region";

    // =========================================================================
    // FLAGS - SET
    // =========================================================================
    public const FLAGS_SET_SUCCESS              = "flags.set.success";
    public const FLAGS_SET_INVALID_FLAG         = "flags.set.invalid-flag";
    public const FLAGS_SET_INVALID_VALUE        = "flags.set.invalid-value";
    public const FLAGS_SET_USAGE                = "flags.set.usage";
    public const FLAGS_SET_FAILURE              = "flags.set.failure";

    // =========================================================================
    // FLAGS - RESET
    // =========================================================================
    public const FLAGS_RESET_SUCCESS            = "flags.reset.success";
    public const FLAGS_RESET_SUCCESS_ALL        = "flags.reset.success-all";
    public const FLAGS_RESET_CONFIRM            = "flags.reset.confirm";
    public const FLAGS_RESET_EXPIRED            = "flags.reset.expired";

    // =========================================================================
    // FLAGS - GET
    // =========================================================================
    public const FLAGS_GET_HEADER               = "flags.get.header";
    public const FLAGS_GET_ENTRY                = "flags.get.entry";
    public const FLAGS_GET_ENTRY_MODIFIED       = "flags.get.entry-modified";
    public const FLAGS_GET_FOOTER               = "flags.get.footer";
    public const FLAGS_GET_SPECIFIC             = "flags.get.specific";

    // =========================================================================
    // FLAGS - GET
    // =========================================================================
    public const FLAGS_LIST_HEADER              = "flags.list.header";
    public const FLAGS_LIST_ENTRY               = "flags.list.entry";

    // =========================================================================
    // DENIED - BLOCKS
    // =========================================================================
    public const DENIED_BLOCK_PLACE             = "denied.block-place";
    public const DENIED_BLOCK_BREAK             = "denied.block-break";

    // =========================================================================
    // DENIED - COMBAT
    // =========================================================================
    public const DENIED_PVP                     = "denied.pvp";
    public const DENIED_ALLOW_DAMAGE_ANIMALS    = "denied.allow-damage-animals";
    public const DENIED_ALLOW_DAMAGE_MONSTERS   = "denied.allow-damage-monsters";

    // =========================================================================
    // DENIED - INTERACTIONS
    // =========================================================================
    public const DENIED_USE                     = "denied.use";
    public const DENIED_INTERACTFRAME           = "denied.interactframe";
    public const DENIED_SLEEP                   = "denied.sleep";

    // =========================================================================
    // DENIED - ITEMS
    // =========================================================================
    public const DENIED_ITEM_DROP               = "denied.item-drop";
    public const DENIED_EAT                     = "denied.eat";
    public const DENIED_ENDERPEARL              = "denied.enderpearl";
    public const DENIED_BOW                     = "denied.bow";
    public const DENIED_POTIONS                 = "denied.potions";

    // =========================================================================
    // DENIED - CHAT & COMMANDS
    // =========================================================================
    public const DENIED_SEND_CHAT               = "denied.send-chat";
    public const DENIED_BLOCKED_CMDS            = "denied.blocked-cmds";
    public const DENIED_ALLOWED_CMDS            = "denied.allowed-cmds";

    // =========================================================================
    // DENIED - MOVEMENTS
    // =========================================================================
    public const DENIED_ALLOWED_ENTER           = "denied.allowed-enter";
    public const DENIED_ALLOWED_LEAVE           = "denied.allowed-leave";

    // =========================================================================
    // NOTIFY
    // =========================================================================
    public const NOTIFY_ENTER_DEFAULT           = "notify.enter.default";
    public const NOTIFY_LEAVE_DEFAULT           = "notify.leave.default";

    // =========================================================================
    // GUI - MAIN
    // =========================================================================
    public const GUI_MAIN_TITLE                 = "gui.main.title";
    public const GUI_MAIN_BUTTON_REGION_MGT     = "gui.main.buttons.region-management";
    public const GUI_MAIN_BUTTON_HELP           = "gui.main.buttons.help";

    // =========================================================================
    // GUI - REGION MANAGEMENT
    // =========================================================================
    public const GUI_REGION_MGT_TITLE           = "gui.region-management.title";
    public const GUI_REGION_MGT_MANAGE_EXISTING = "gui.region-management.buttons.manage-existing";
    public const GUI_REGION_MGT_CREATE          = "gui.region-management.buttons.create-region";
    public const GUI_REGION_MGT_REDEFINE        = "gui.region-management.buttons.redefine-region";
    public const GUI_REGION_MGT_DELETE          = "gui.region-management.buttons.delete-region";

    // =========================================================================
    // GUI - CREATE REGION
    // =========================================================================
    public const GUI_CREATE_REGION_TITLE        = "gui.create-region.title";
    public const GUI_CREATE_REGION_INSTRUCTIONS = "gui.create-region.labels.instructions";
    public const GUI_CREATE_REGION_NAME_INPUT   = "gui.create-region.labels.name-input";
    public const GUI_CREATE_REGION_NAME_HOLDER  = "gui.create-region.labels.name-placeholder";
    public const GUI_CREATE_REGION_EXTENDED_TOG = "gui.create-region.labels.extended-toggle";
    public const GUI_CREATE_REGION_EXTENDED_INF = "gui.create-region.labels.extended-info";

    // =========================================================================
    // GUI - SELECT REGION
    // =========================================================================
    public const GUI_SELECT_REGION_TITLE        = "gui.select-region.title";
    public const GUI_SELECT_REGION_DROPDOWN     = "gui.select-region.labels.dropdown";

    // =========================================================================
    // GUI - REGION FLAGS
    // =========================================================================
    public const GUI_REGION_FLAGS_TITLE         = "gui.region-flags.title";
    public const GUI_REGION_FLAGS_TOGGLE        = "gui.region-flags.labels.flag-toggle";
    public const GUI_REGION_FLAGS_INPUT         = "gui.region-flags.labels.flag-input";
    public const GUI_REGION_FLAGS_CURRENT_VAL   = "gui.region-flags.labels.current-value";

    // =========================================================================
    // GUI - DELETE CONFIRM
    // =========================================================================
    public const GUI_DELETE_CONFIRM_TITLE       = "gui.delete-confirm.title";
    public const GUI_DELETE_CONFIRM_WARNING     = "gui.delete-confirm.labels.warning";
    public const GUI_DELETE_CONFIRM_CONFIRM_BTN = "gui.delete-confirm.labels.confirm-button";
    public const GUI_DELETE_CONFIRM_CANCEL_BTN  = "gui.delete-confirm.labels.cancel-button";

    // =========================================================================
    // HELP
    // =========================================================================
    public const HELP_HEADER                    = "help.header";
    public const HELP_COMMANDS                  = "help.commands";
    public const HELP_FOOTER                    = "help.footer";
    public const HELP_FLAGS_HEADER              = "help.flags.header";
    public const HELP_FLAGS_LIST                = "help.flags.list";

    // =========================================================================
    // WAND
    // =========================================================================
    public const WAND_GIVE_SUCCESS              = "wand.give.success";
    public const WAND_GIVE_ALREADY_HAS          = "wand.give.already-has";
    public const WAND_POS_1                     = "wand.pos1";
    public const WAND_POS_2                     = "wand.pos2";

    // =========================================================================
    // BYPASS
    // =========================================================================
    public const BYPASS_ENABLED                 = "bypass.enabled";
    public const BYPASS_DISABLED                = "bypass.disabled";
    public const BYPASS_ACTIVE_WARNING          = "bypass.active-warning";

    // =========================================================================
    // EVENTS
    // =========================================================================
    public const EVENTS_GAMEMODE_CHANGED        = "events.gamemode-changed";
    public const EVENTS_FLY_ENABLED             = "events.fly-enabled";
    public const EVENTS_FLY_DISABLED            = "events.fly-disabled";
    public const EVENTS_EFFECTS_APPLIED         = "events.effects-applied";
    public const EVENTS_EFFECTS_REMOVED         = "events.effects-removed";

    // =========================================================================
    // ERRORS
    // =========================================================================
    public const ERRORS_WORLD_NOT_FOUND         = "errors.world-not-found";
    public const ERRORS_REGION_NOT_FOUND        = "errors.region-not-found";
    public const ERRORS_SAVE_FAILED             = "errors.save-failed";
    public const ERRORS_LOAD_FAILED             = "errors.load-failed";
    public const ERRORS_INVALID_POSITION        = "errors.invalid-position";
    public const ERRORS_SELECTION_INCOMPLETE    = "errors.selection-incomplete";
    public const ERRORS_INTERNAL_ERROR          = "errors.internal-error";

    // =========================================================================
    // DEBUG
    // =========================================================================
    public const DEBUG_REGION_ENTER             = "debug.region-enter";
    public const DEBUG_REGION_LEAVE             = "debug.region-leave";
    public const DEBUG_FLAG_CHECK               = "debug.flag-check";
    public const DEBUG_EVENT_CANCELLED          = "debug.event-cancelled";

    // =========================================================================
    // CANCEL
    // =========================================================================
    public const CANCEL_NOTHING_TO_CANCEL       = "cancel.nothing-to-cancel";
    public const CANCEL_CREATION_CANCELLED      = "cancel.creation-cancelled";
    public const CANCEL_OPERATION_CANCELLED     = "cancel.operation-cancelled";

    // =========================================================================
    // PARENT
    // =========================================================================
    public const PARENT_SET_SUCCESS             = "parent.set.success";
    public const PARENT_SET_FAILED              = "parent.set.failed";
    public const PARENT_SET_USAGE               = "parent.set.usage";
    public const PARENT_SELF_REFERENCE          = "parent.self-reference";
    public const PARENT_CIRCULAR_REFERENCE      = "parent.circular-reference";
    public const PARENT_REMOVE_SUCCESS          = "parent.remove.success";
    public const PARENT_NO_PARENT               = "parent.no-parent";
    public const PARENT_INFO_HEADER             = "parent.info.header";
    public const PARENT_INFO_PARENT             = "parent.info.parent";
    public const PARENT_INFO_CHAIN              = "parent.info.chain";
    public const PARENT_INFO_CHILDREN           = "parent.info.children";
    public const PARENT_INFO_DESCENDANTS        = "parent.info.descendants";
    public const PARENT_INFO_INHERITED_FLAGS    = "parent.info.inherited-flags";

}