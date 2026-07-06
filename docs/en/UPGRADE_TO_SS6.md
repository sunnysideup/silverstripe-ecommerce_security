# Upgrade Guide: Moving to Silverstripe CMS 6

This document outlines the necessary steps and breaking changes required to upgrade your project to the version compatible with Silverstripe CMS 6.

## New Requirements

-   **Silverstripe CMS 6**: This version is only compatible with `silverstripe/recipe-cms: ^6.0`.
-   **PHP 8.1+**: The use of `#[Override]` attributes and other syntax indicates a requirement for at least PHP 8.1.
-   **Ecommerce Module**: The dependency on `sunnysideup/ecommerce` has been updated to `^33.0`.

## ⚠️ BREAKING CHANGES

### Dependency Updates

Your project's `composer.json` must be updated to reflect the new major versions:
- `sunnysideup/ecommerce`: `5.x-dev` -> `^33.0`
- `silverstripe/recipe-cms`: `^4.0 || ^5.0` -> `^6.0`

### API Changes & Deprecations

#### Framework Method Overrides

The codebase now uses the `#[Override]` attribute for all methods that override parent framework or base class methods (e.g., `canCreate`, `canEdit`, `getCMSFields`, `onAfterWrite`, etc.). If you have subclassed any of the updated classes, you should review your own implementations to ensure they are still compatible and add the `#[Override]` attribute where appropriate.

#### `DataObject::get_one()` Removed

The deprecated `DataObject::get_one()` method has been removed. All instances have been replaced with the standard ORM query `MyClass::get()->first()`.

**Example:**
-   **Old**: `DataObject::get_one(OrderStepSecurityCheck::class)`
-   **New**: `OrderStepSecurityCheck::get()->setUseCache(true)->first()`

You must update any direct calls to this method in your own code.

#### Removed `parent::onBeforeWrite()` Calls

Calls to `parent::onBeforeWrite()` within `EcommerceSecurityMemberDecoration` and `EcommerceSecurityOrderDecoration` have been removed.

🚨 **CRITICAL REVIEW REQUIRED / RISKY:**
**These `onBeforeWrite` methods are part of a `SilverStripe\ORM\Extension` and do not have a parent method to call. The previous code was incorrect and would have caused a fatal error if not for PHP's loose handling in older versions. While this change fixes a bug, you should verify if your project relied on any unexpected side-effects of this incorrect call.**

---

*This guide focuses on the most critical, risky, and breaking changes. Trivial changes such as minor syntax updates, the addition of blank lines, or the introduction of the `Override` attribute have been omitted for brevity.*