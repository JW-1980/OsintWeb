---
name: Design Guidelines & UI Consistency
description: Comprehensive design guidelines for consistent UI/UX across Flutter mobile app and Laravel/Vue web application
version: 1.0.1
tags: [design, ui, ux, consistency, flutter, laravel, vue, tailwind, styling]
trigger_keywords: ["sk-Design Guidelines & UI Consistency", design guidelines, ui consistency, design system, style guide, ui patterns, design standards, component design]
globs:
  - "**/*.vue"
  - "**/*.blade.php"
  - "**/*.dart"
  - "**/*.css"
  - "**/*.scss"
  - "**/tailwind.config.js"
  - "**/theme*.dart"
---

# Design Guidelines & UI Consistency Skill

You are an expert in maintaining design consistency across the Boekhouder application. This skill ensures all UI elements follow the established design system for both the Flutter mobile app and Laravel/Vue web application.

## When to Use

This skill MUST be applied:

1. **Before completing any feature** - Review UI for consistency
2. **When creating new UI components** - Ensure they match existing patterns
3. **When modifying existing UI** - Maintain consistency with surrounding elements
4. **During code reviews** - Check design compliance
5. **When building new pages/screens** - Follow layout patterns

---

## 1. Color System

### Primary Brand Colors

```
Primary Blue:     #3B82F6 (blue-500)    - Main brand color, CTAs, links
Primary Dark:     #1E40AF (blue-800)    - Dark mode primary, headers
Primary Light:    #DBEAFE (blue-100)    - Backgrounds, highlights

Secondary Green:  #10B981 (emerald-500) - Success, positive actions
Secondary Orange: #F59E0B (amber-500)   - Warnings, attention
Secondary Red:    #EF4444 (red-500)     - Errors, destructive actions
```

### Semantic Colors

| Purpose | Light Mode | Dark Mode | Usage |
|---------|------------|-----------|-------|
| **Success** | `#22C55E` (green-500) | `#4ADE80` (green-400) | Confirmations, positive feedback |
| **Warning** | `#F59E0B` (amber-500) | `#FBBF24` (amber-400) | Cautions, pending states |
| **Error** | `#EF4444` (red-500) | `#F87171` (red-400) | Errors, destructive actions |
| **Info** | `#3B82F6` (blue-500) | `#60A5FA` (blue-400) | Informational messages |

### Neutral Colors

```
Background:
- Light: #FFFFFF (white)      Dark: #111827 (gray-900)
- Light: #F9FAFB (gray-50)    Dark: #1F2937 (gray-800)
- Light: #F3F4F6 (gray-100)   Dark: #374151 (gray-700)

Text:
- Primary:   #111827 (gray-900)  Dark: #F9FAFB (gray-50)
- Secondary: #6B7280 (gray-500)  Dark: #9CA3AF (gray-400)
- Muted:     #9CA3AF (gray-400)  Dark: #6B7280 (gray-500)

Borders:
- Default:  #E5E7EB (gray-200)   Dark: #374151 (gray-700)
- Focused:  #3B82F6 (blue-500)   Dark: #60A5FA (blue-400)
```

### Laravel/Vue (Tailwind CSS)

```html
<!-- Primary button -->
<button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
  Primary Action
</button>

<!-- Success alert -->
<div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
  Success message
</div>

<!-- Error state -->
<div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
  Error message
</div>
```

### Flutter (Dart)

```dart
// Theme colors
class AppColors {
  // Primary
  static const primary = Color(0xFF3B82F6);
  static const primaryDark = Color(0xFF1E40AF);
  static const primaryLight = Color(0xFFDBEAFE);

  // Semantic
  static const success = Color(0xFF22C55E);
  static const warning = Color(0xFFF59E0B);
  static const error = Color(0xFFEF4444);
  static const info = Color(0xFF3B82F6);

  // Neutrals
  static const background = Color(0xFFFFFFFF);
  static const backgroundDark = Color(0xFF111827);
  static const textPrimary = Color(0xFF111827);
  static const textSecondary = Color(0xFF6B7280);
  static const border = Color(0xFFE5E7EB);
}
```

---

## 2. Typography

### Font Family

```
Primary Font: Inter (sans-serif)
- Used for all body text, labels, and UI elements
- Weights: 400 (Regular), 500 (Medium), 600 (Semi-bold), 700 (Bold)

Display Font: Inter (or Playfair Display for special headers)
- Used for marketing pages, large headlines

Monospace: JetBrains Mono
- Used for code, invoice numbers, financial figures
```

### Type Scale

| Level | Size | Line Height | Weight | Usage |
|-------|------|-------------|--------|-------|
| **Display** | 36px / 2.25rem | 1.2 | 700 | Hero sections, landing pages |
| **H1** | 30px / 1.875rem | 1.2 | 700 | Page titles |
| **H2** | 24px / 1.5rem | 1.3 | 600 | Section headers |
| **H3** | 20px / 1.25rem | 1.4 | 600 | Card titles, subsections |
| **H4** | 18px / 1.125rem | 1.4 | 600 | Small headers |
| **Body** | 16px / 1rem | 1.5 | 400 | Default text |
| **Body Small** | 14px / 0.875rem | 1.5 | 400 | Secondary text, captions |
| **Caption** | 12px / 0.75rem | 1.4 | 400 | Labels, hints, timestamps |

### Laravel/Vue Typography

```html
<!-- Headings -->
<h1 class="text-3xl font-bold text-gray-900">Page Title</h1>
<h2 class="text-2xl font-semibold text-gray-900">Section Header</h2>
<h3 class="text-xl font-semibold text-gray-800">Card Title</h3>

<!-- Body text -->
<p class="text-base text-gray-700">Regular paragraph text</p>
<p class="text-sm text-gray-500">Secondary/helper text</p>
<span class="text-xs text-gray-400">Caption or timestamp</span>

<!-- Financial numbers (monospace) -->
<span class="font-mono text-lg font-semibold">€ 1.234,56</span>
```

### Flutter Typography

```dart
// Text theme
final textTheme = TextTheme(
  displayLarge: TextStyle(fontSize: 36, fontWeight: FontWeight.w700, height: 1.2),
  headlineLarge: TextStyle(fontSize: 30, fontWeight: FontWeight.w700, height: 1.2),
  headlineMedium: TextStyle(fontSize: 24, fontWeight: FontWeight.w600, height: 1.3),
  headlineSmall: TextStyle(fontSize: 20, fontWeight: FontWeight.w600, height: 1.4),
  titleLarge: TextStyle(fontSize: 18, fontWeight: FontWeight.w600, height: 1.4),
  bodyLarge: TextStyle(fontSize: 16, fontWeight: FontWeight.w400, height: 1.5),
  bodyMedium: TextStyle(fontSize: 14, fontWeight: FontWeight.w400, height: 1.5),
  bodySmall: TextStyle(fontSize: 12, fontWeight: FontWeight.w400, height: 1.4),
);
```

---

## 3. Spacing System

### Base Unit: 4px

Use multiples of 4px for all spacing:

| Token | Value | Usage |
|-------|-------|-------|
| `xs` | 4px | Tight spacing, icon gaps |
| `sm` | 8px | Small gaps, compact lists |
| `md` | 16px | Default spacing, card padding |
| `lg` | 24px | Section spacing |
| `xl` | 32px | Large gaps, page sections |
| `2xl` | 48px | Major sections |
| `3xl` | 64px | Page margins, hero sections |

### Component Spacing

```
Card Padding: 16px (md) to 24px (lg)
Form Field Gap: 16px (md)
Button Padding: 8px 16px (sm/md) to 12px 24px (md/lg)
Section Margin: 32px (xl) to 48px (2xl)
Page Container: max-width 1280px, padding 16px mobile / 32px desktop
```

### Laravel/Vue Spacing

```html
<!-- Card with proper spacing -->
<div class="bg-white rounded-lg shadow p-4 md:p-6 space-y-4">
  <h3 class="text-xl font-semibold">Card Title</h3>
  <p class="text-gray-600">Card content</p>
</div>

<!-- Form with consistent spacing -->
<form class="space-y-4">
  <div class="space-y-2">
    <label class="text-sm font-medium">Label</label>
    <input class="w-full px-3 py-2 border rounded-lg" />
  </div>
</form>

<!-- Section spacing -->
<section class="py-8 md:py-12">
  <!-- Content -->
</section>
```

### Flutter Spacing

```dart
// Spacing constants
class AppSpacing {
  static const xs = 4.0;
  static const sm = 8.0;
  static const md = 16.0;
  static const lg = 24.0;
  static const xl = 32.0;
  static const xxl = 48.0;
}

// Usage
Padding(
  padding: EdgeInsets.all(AppSpacing.md),
  child: Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      Text('Title', style: Theme.of(context).textTheme.headlineSmall),
      SizedBox(height: AppSpacing.sm),
      Text('Content'),
    ],
  ),
)
```

---

## 4. Border Radius

### Radius Scale

| Token | Value | Usage |
|-------|-------|-------|
| `none` | 0px | Sharp corners (rarely used) |
| `sm` | 4px | Small elements, tags, chips |
| `md` | 8px | Buttons, inputs, small cards |
| `lg` | 12px | Cards, modals, dropdowns |
| `xl` | 16px | Large cards, containers |
| `2xl` | 24px | Feature cards, hero elements |
| `full` | 9999px | Pills, avatars, circular buttons |

### Laravel/Vue Border Radius

```html
<!-- Small elements -->
<span class="rounded bg-blue-100 text-blue-800 px-2 py-1 text-xs">Tag</span>

<!-- Buttons and inputs -->
<button class="rounded-lg px-4 py-2">Button</button>
<input class="rounded-lg border px-3 py-2" />

<!-- Cards -->
<div class="rounded-xl bg-white shadow p-6">Card content</div>

<!-- Avatars -->
<img class="rounded-full w-10 h-10" src="avatar.jpg" />
```

### Flutter Border Radius

```dart
// Border radius constants
class AppRadius {
  static const sm = 4.0;
  static const md = 8.0;
  static const lg = 12.0;
  static const xl = 16.0;
}

// Card decoration
Container(
  decoration: BoxDecoration(
    color: Colors.white,
    borderRadius: BorderRadius.circular(AppRadius.lg),
    boxShadow: [
      BoxShadow(
        color: Colors.black.withOpacity(0.1),
        blurRadius: 10,
        offset: Offset(0, 4),
      ),
    ],
  ),
)
```

---

## 5. Shadows & Elevation

### Shadow Scale

| Level | Usage | CSS | Flutter |
|-------|-------|-----|---------|
| **sm** | Hover states, subtle depth | `shadow-sm` | `elevation: 1` |
| **md** | Cards, dropdowns | `shadow` | `elevation: 2` |
| **lg** | Modals, popovers | `shadow-lg` | `elevation: 4` |
| **xl** | Dialogs, floating elements | `shadow-xl` | `elevation: 8` |

### Laravel/Vue Shadows

```html
<!-- Card with shadow -->
<div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow">
  Card content
</div>

<!-- Dropdown shadow -->
<div class="absolute bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
  Dropdown content
</div>

<!-- Modal shadow -->
<div class="bg-white rounded-xl shadow-xl">
  Modal content
</div>
```

### Flutter Shadows

```dart
// Card with shadow
Container(
  decoration: BoxDecoration(
    color: Colors.white,
    borderRadius: BorderRadius.circular(12),
    boxShadow: [
      BoxShadow(
        color: Colors.black.withOpacity(0.1),
        blurRadius: 10,
        offset: Offset(0, 4),
      ),
    ],
  ),
)

// Or use Material elevation
Card(
  elevation: 2,
  shape: RoundedRectangleBorder(
    borderRadius: BorderRadius.circular(12),
  ),
)
```

---

## 6. Button Styles

### Button Variants

| Variant | Usage | Style |
|---------|-------|-------|
| **Primary** | Main CTAs, submit actions | Solid blue background |
| **Secondary** | Alternative actions | White/transparent with border |
| **Danger** | Destructive actions | Solid red background |
| **Ghost** | Subtle actions | Transparent, text only |
| **Link** | Navigation, inline actions | Text with underline on hover |

### Button Sizes

| Size | Padding | Font Size | Height |
|------|---------|-----------|--------|
| **sm** | 6px 12px | 14px | 32px |
| **md** | 8px 16px | 14px | 40px |
| **lg** | 12px 24px | 16px | 48px |

### Laravel/Vue Buttons

```html
<!-- Primary button -->
<button class="inline-flex items-center justify-center px-4 py-2
               bg-blue-500 hover:bg-blue-600 text-white font-medium
               rounded-lg transition-colors focus:outline-none
               focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
               disabled:opacity-50 disabled:cursor-not-allowed">
  <svg class="w-5 h-5 mr-2"><!-- Icon --></svg>
  Primary Action
</button>

<!-- Secondary button -->
<button class="inline-flex items-center justify-center px-4 py-2
               bg-white hover:bg-gray-50 text-gray-700 font-medium
               border border-gray-300 rounded-lg transition-colors
               focus:outline-none focus:ring-2 focus:ring-blue-500">
  Secondary
</button>

<!-- Danger button -->
<button class="inline-flex items-center justify-center px-4 py-2
               bg-red-500 hover:bg-red-600 text-white font-medium
               rounded-lg transition-colors">
  Delete
</button>

<!-- Ghost button -->
<button class="inline-flex items-center justify-center px-4 py-2
               text-gray-600 hover:text-gray-900 hover:bg-gray-100
               font-medium rounded-lg transition-colors">
  Cancel
</button>
```

### Flutter Buttons

```dart
// Primary button
ElevatedButton(
  onPressed: () {},
  style: ElevatedButton.styleFrom(
    backgroundColor: AppColors.primary,
    foregroundColor: Colors.white,
    padding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(8),
    ),
  ),
  child: Text('Primary Action'),
)

// Secondary button
OutlinedButton(
  onPressed: () {},
  style: OutlinedButton.styleFrom(
    foregroundColor: AppColors.textPrimary,
    side: BorderSide(color: AppColors.border),
    padding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(8),
    ),
  ),
  child: Text('Secondary'),
)

// Text/Ghost button
TextButton(
  onPressed: () {},
  child: Text('Cancel'),
)
```

---

## 7. Form Elements

### Input Fields

```html
<!-- Laravel/Vue Input -->
<div class="space-y-1">
  <label class="block text-sm font-medium text-gray-700">
    Label <span class="text-red-500">*</span>
  </label>
  <input type="text"
         class="w-full px-3 py-2 border border-gray-300 rounded-lg
                focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                placeholder-gray-400 transition-colors"
         placeholder="Placeholder text" />
  <p class="text-sm text-gray-500">Helper text here</p>
</div>

<!-- Error state -->
<input class="w-full px-3 py-2 border border-red-500 rounded-lg
              focus:ring-2 focus:ring-red-500 bg-red-50" />
<p class="text-sm text-red-600">Error message</p>
```

```dart
// Flutter Input
TextField(
  decoration: InputDecoration(
    labelText: 'Label',
    hintText: 'Placeholder text',
    helperText: 'Helper text here',
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(8),
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(8),
      borderSide: BorderSide(color: AppColors.primary, width: 2),
    ),
    errorBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(8),
      borderSide: BorderSide(color: AppColors.error),
    ),
  ),
)
```

### Select / Dropdown

```html
<!-- Laravel/Vue Select -->
<select class="w-full px-3 py-2 border border-gray-300 rounded-lg
               focus:ring-2 focus:ring-blue-500 focus:border-blue-500
               bg-white">
  <option value="">Select an option</option>
  <option value="1">Option 1</option>
</select>
```

```dart
// Flutter Dropdown
DropdownButtonFormField<String>(
  decoration: InputDecoration(
    labelText: 'Select Option',
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(8),
    ),
  ),
  items: options.map((option) =>
    DropdownMenuItem(value: option, child: Text(option))
  ).toList(),
  onChanged: (value) {},
)
```

---

## 8. Cards & Containers

### Card Patterns

```html
<!-- Laravel/Vue Standard Card -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <!-- Card Header -->
  <div class="px-6 py-4 border-b border-gray-100">
    <h3 class="text-lg font-semibold text-gray-900">Card Title</h3>
    <p class="text-sm text-gray-500">Optional subtitle</p>
  </div>

  <!-- Card Body -->
  <div class="px-6 py-4">
    <p class="text-gray-700">Card content goes here</p>
  </div>

  <!-- Card Footer (optional) -->
  <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
    <button class="text-sm text-blue-600 hover:text-blue-700 font-medium">
      Action
    </button>
  </div>
</div>
```

```dart
// Flutter Standard Card
Card(
  elevation: 1,
  shape: RoundedRectangleBorder(
    borderRadius: BorderRadius.circular(12),
  ),
  child: Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      // Header
      Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Card Title', style: Theme.of(context).textTheme.titleMedium),
            Text('Subtitle', style: Theme.of(context).textTheme.bodySmall),
          ],
        ),
      ),
      Divider(height: 1),
      // Body
      Padding(
        padding: EdgeInsets.all(16),
        child: Text('Card content'),
      ),
    ],
  ),
)
```

---

## 9. Tables & Lists

### Table Styling

```html
<!-- Laravel/Vue Table -->
<div class="overflow-x-auto rounded-lg border border-gray-200">
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
          Column
        </th>
      </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
          Cell content
        </td>
      </tr>
    </tbody>
  </table>
</div>
```

```dart
// Flutter DataTable
DataTable(
  headingRowColor: MaterialStateProperty.all(Colors.grey[50]),
  columns: [
    DataColumn(
      label: Text('Column', style: TextStyle(fontWeight: FontWeight.w600)),
    ),
  ],
  rows: [
    DataRow(cells: [DataCell(Text('Cell content'))]),
  ],
)
```

---

## 10. Navigation & Headers

### Page Header

```html
<!-- Laravel/Vue Page Header -->
<div class="mb-8">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Page Title</h1>
      <p class="mt-1 text-sm text-gray-500">Page description or breadcrumb</p>
    </div>
    <div class="flex items-center gap-3">
      <button class="btn-secondary">Secondary Action</button>
      <button class="btn-primary">Primary Action</button>
    </div>
  </div>
</div>
```

```dart
// Flutter App Bar
AppBar(
  title: Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      Text('Page Title'),
      Text('Subtitle', style: TextStyle(fontSize: 12, fontWeight: FontWeight.normal)),
    ],
  ),
  actions: [
    IconButton(icon: Icon(Icons.add), onPressed: () {}),
  ],
)
```

---

## 11. Responsive Breakpoints

### Breakpoint Scale

| Name | Min Width | Tailwind | Flutter |
|------|-----------|----------|---------|
| **xs** | 0px | Default | `<600` |
| **sm** | 640px | `sm:` | `>=600` |
| **md** | 768px | `md:` | `>=900` |
| **lg** | 1024px | `lg:` | `>=1200` |
| **xl** | 1280px | `xl:` | `>=1536` |

### Responsive Patterns

```html
<!-- Laravel/Vue Responsive Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
  <!-- Cards -->
</div>

<!-- Responsive padding -->
<div class="px-4 sm:px-6 lg:px-8">
  <!-- Content -->
</div>

<!-- Hide/show on breakpoints -->
<div class="hidden md:block">Desktop only</div>
<div class="md:hidden">Mobile only</div>
```

```dart
// Flutter Responsive Layout
LayoutBuilder(
  builder: (context, constraints) {
    if (constraints.maxWidth >= 1200) {
      return _buildDesktopLayout();
    } else if (constraints.maxWidth >= 600) {
      return _buildTabletLayout();
    } else {
      return _buildMobileLayout();
    }
  },
)
```

---

## 12. Dark Mode Support

### Color Mapping

All components must support dark mode. Use CSS variables or theme providers:

```html
<!-- Laravel/Vue Dark Mode -->
<div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
  <h2 class="text-gray-900 dark:text-white">Heading</h2>
  <p class="text-gray-600 dark:text-gray-300">Body text</p>
  <div class="border-gray-200 dark:border-gray-700">Border</div>
</div>
```

```dart
// Flutter Theme
ThemeData(
  brightness: Brightness.dark,
  scaffoldBackgroundColor: Color(0xFF111827),
  cardColor: Color(0xFF1F2937),
  textTheme: TextTheme(
    bodyLarge: TextStyle(color: Color(0xFFF9FAFB)),
  ),
)
```

---

## 13. Accessibility Requirements

### Color Contrast

- **Text on backgrounds**: Minimum 4.5:1 contrast ratio
- **Large text (18px+)**: Minimum 3:1 contrast ratio
- **UI components**: Minimum 3:1 contrast ratio

### Focus States

All interactive elements MUST have visible focus states:

```html
<!-- Laravel/Vue Focus States -->
<button class="focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
  Button
</button>

<input class="focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />

<a class="focus:outline-none focus:ring-2 focus:ring-blue-500 rounded">
  Link
</a>
```

```dart
// Flutter Focus
Focus(
  child: Container(
    decoration: BoxDecoration(
      border: Border.all(
        color: Theme.of(context).focusColor,
        width: 2,
      ),
    ),
  ),
)
```

### Semantic HTML/Widgets

- Use proper heading hierarchy (h1 > h2 > h3)
- Use `<button>` for actions, `<a>` for navigation
- Include `aria-label` for icon-only buttons
- Ensure all images have `alt` text
- Use `Semantics` widget in Flutter for screen readers

---

## 14. Animation Guidelines

### Timing

| Type | Duration | Easing |
|------|----------|--------|
| **Micro** (hover, focus) | 150ms | ease-out |
| **Small** (toggle, fade) | 200ms | ease-in-out |
| **Medium** (expand, slide) | 300ms | ease-in-out |
| **Large** (page transition) | 400-500ms | ease-in-out |

### Laravel/Vue Transitions

```css
/* Base transition */
.transition-all {
  transition-property: all;
  transition-duration: 200ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Hover transitions */
.hover-lift {
  transition: transform 150ms ease-out, box-shadow 150ms ease-out;
}
.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
```

### Flutter Animations

```dart
// Implicit animations
AnimatedContainer(
  duration: Duration(milliseconds: 200),
  curve: Curves.easeInOut,
  // Properties that animate
)

// Hero animations for navigation
Hero(
  tag: 'item-$id',
  child: Container(),
)
```

---

## 15. Icon Guidelines

### Icon Sources

- **Primary**: Heroicons (outlined style)
- **Alternative**: Lucide Icons
- **Flutter**: Material Icons or custom SVG

### Icon Sizes

| Context | Size | Tailwind | Flutter |
|---------|------|----------|---------|
| **Inline text** | 16px | `w-4 h-4` | `size: 16` |
| **Buttons** | 20px | `w-5 h-5` | `size: 20` |
| **Navigation** | 24px | `w-6 h-6` | `size: 24` |
| **Empty states** | 48px | `w-12 h-12` | `size: 48` |

### Icon Button Pattern

```html
<!-- Laravel/Vue Icon Button -->
<button class="p-2 rounded-lg text-gray-500 hover:text-gray-700
               hover:bg-gray-100 transition-colors"
        aria-label="Action description">
  <svg class="w-5 h-5"><!-- Icon --></svg>
</button>
```

```dart
// Flutter Icon Button
IconButton(
  icon: Icon(Icons.edit, size: 20),
  tooltip: 'Edit',
  onPressed: () {},
)
```

---

## 16. Design Checklist

### Before Completing Any UI Work

**Colors**
- [ ] Using colors from the defined palette only
- [ ] Proper contrast ratios for accessibility
- [ ] Dark mode colors applied correctly

**Typography**
- [ ] Using defined type scale
- [ ] Proper heading hierarchy
- [ ] Line heights and spacing correct

**Spacing**
- [ ] Using 4px-based spacing system
- [ ] Consistent padding/margins
- [ ] Proper component gaps

**Components**
- [ ] Button variants match guidelines
- [ ] Form inputs follow patterns
- [ ] Cards use standard styling
- [ ] Tables/lists are properly styled

**Responsiveness**
- [ ] Works on mobile (320px+)
- [ ] Tablet layout verified
- [ ] Desktop layout verified

**Accessibility**
- [ ] Focus states visible
- [ ] Proper ARIA labels
- [ ] Semantic HTML/widgets used
- [ ] Screen reader compatible

**Consistency**
- [ ] Matches existing UI patterns
- [ ] Same interaction patterns
- [ ] Consistent iconography
- [ ] Animation timing matches

---

## Version History

### Version 1.0.0 (2025-12-16)
- Initial design guidelines document
- Color system with light/dark mode
- Typography and spacing scales
- Component patterns for buttons, forms, cards
- Accessibility requirements
- Responsive breakpoints
- Animation guidelines
