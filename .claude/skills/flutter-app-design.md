---
name: flutter-app-design
description: Cross-platform Flutter app design for Android, iOS, Windows, and Linux
version: 1.0.1
tags: [flutter, mobile, android, ios, windows, linux, ui, ux, material-design, cupertino, adaptive]
trigger_keywords: [sk-flutter-app-design, flutter app design, mobile app design, cross-platform design, material design, cupertino design, adaptive ui, flutter ui patterns]
---

# Flutter App Design for Multiple Platforms

This skill helps with designing and implementing cross-platform Flutter applications that provide native-feeling experiences on Android, iOS, Windows, and Linux.

## When to Use

- Building new Flutter mobile/desktop apps
- Implementing adaptive UI that works across platforms
- Creating platform-specific experiences
- Designing responsive layouts for different screen sizes
- Implementing navigation patterns
- Optimizing for touch and keyboard/mouse input
- Ensuring platform consistency

## Platform Overview

### Android
- **Design Language**: Material Design 3
- **Navigation**: Bottom navigation, navigation drawer, app bar
- **Widgets**: Material widgets (MaterialApp, Scaffold, etc.)
- **Target Devices**: Phones, tablets, foldables

### iOS
- **Design Language**: Human Interface Guidelines
- **Navigation**: Tab bar, navigation bar, modal sheets
- **Widgets**: Cupertino widgets (CupertinoApp, CupertinoPageScaffold, etc.)
- **Target Devices**: iPhone, iPad

### Windows
- **Design Language**: Fluent Design (adapted)
- **Navigation**: Navigation pane, command bar
- **Input**: Mouse, keyboard, touch
- **Target Devices**: Desktop, laptops, tablets

### Linux
- **Design Language**: GTK/GNOME-style or Material (adapted)
- **Navigation**: Sidebar navigation, header bar
- **Input**: Mouse, keyboard
- **Target Devices**: Desktop workstations, laptops
- **Window Management**: Native window decorations or custom
- **Considerations**: Multiple distro support (Ubuntu, Fedora, Arch)

## Adaptive Design Strategy

### 1. Platform-Aware Widgets

**Use platform-specific widgets when appropriate**:
```dart
import 'dart:io' show Platform;
import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';

class PlatformButton extends StatelessWidget {
  final String text;
  final VoidCallback onPressed;

  const PlatformButton({
    required this.text,
    required this.onPressed,
    Key? key,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    if (Platform.isIOS) {
      return CupertinoButton.filled(
        onPressed: onPressed,
        child: Text(text),
      );
    }

    // Material for Android and Windows
    return ElevatedButton(
      onPressed: onPressed,
      child: Text(text),
    );
  }
}
```

### 2. Adaptive App Structure

**Single codebase with platform adaptations**:
```dart
import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    // Detect platform and return appropriate app wrapper
    if (Platform.isIOS) {
      return CupertinoApp(
        title: 'Boekhouder',
        theme: CupertinoThemeData(
          primaryColor: CupertinoColors.systemBlue,
        ),
        home: HomePage(),
      );
    }

    return MaterialApp(
      title: 'Boekhouder',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.blue),
        useMaterial3: true,
      ),
      home: HomePage(),
    );
  }
}
```

### 3. Responsive Layout Builder

**Adapt to screen size and orientation**:
```dart
class ResponsiveLayout extends StatelessWidget {
  final Widget mobile;
  final Widget? tablet;
  final Widget? desktop;

  const ResponsiveLayout({
    required this.mobile,
    this.tablet,
    this.desktop,
    Key? key,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        // Desktop layout (> 1200px)
        if (constraints.maxWidth >= 1200) {
          return desktop ?? tablet ?? mobile;
        }

        // Tablet layout (> 600px)
        if (constraints.maxWidth >= 600) {
          return tablet ?? mobile;
        }

        // Mobile layout
        return mobile;
      },
    );
  }
}

// Usage:
ResponsiveLayout(
  mobile: InvoiceListMobile(),
  tablet: InvoiceListTablet(),
  desktop: InvoiceListDesktop(),
)
```

## Navigation Patterns

### 1. Bottom Navigation (Mobile)

**Android & iOS phones**:
```dart
class MainScreen extends StatefulWidget {
  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _currentIndex = 0;

  final List<Widget> _screens = [
    DashboardScreen(),
    InvoicesScreen(),
    ExpensesScreen(),
    ReportsScreen(),
    SettingsScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    if (Platform.isIOS) {
      return CupertinoTabScaffold(
        tabBar: CupertinoTabBar(
          currentIndex: _currentIndex,
          onTap: (index) => setState(() => _currentIndex = index),
          items: const [
            BottomNavigationBarItem(
              icon: Icon(CupertinoIcons.house),
              label: 'Dashboard',
            ),
            BottomNavigationBarItem(
              icon: Icon(CupertinoIcons.doc_text),
              label: 'Facturen',
            ),
            BottomNavigationBarItem(
              icon: Icon(CupertinoIcons.money_dollar),
              label: 'Uitgaven',
            ),
            BottomNavigationBarItem(
              icon: Icon(CupertinoIcons.chart_bar),
              label: 'Rapporten',
            ),
            BottomNavigationBarItem(
              icon: Icon(CupertinoIcons.settings),
              label: 'Instellingen',
            ),
          ],
        ),
        tabBuilder: (context, index) {
          return CupertinoTabView(
            builder: (context) => _screens[index],
          );
        },
      );
    }

    return Scaffold(
      body: _screens[_currentIndex],
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: (index) {
          setState(() => _currentIndex = index);
        },
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.dashboard),
            label: 'Dashboard',
          ),
          NavigationDestination(
            icon: Icon(Icons.receipt),
            label: 'Facturen',
          ),
          NavigationDestination(
            icon: Icon(Icons.account_balance_wallet),
            label: 'Uitgaven',
          ),
          NavigationDestination(
            icon: Icon(Icons.analytics),
            label: 'Rapporten',
          ),
          NavigationDestination(
            icon: Icon(Icons.settings),
            label: 'Instellingen',
          ),
        ],
      ),
    );
  }
}
```

### 2. Navigation Rail (Tablet/Desktop)

**For larger screens**:
```dart
class DesktopMainScreen extends StatefulWidget {
  @override
  State<DesktopMainScreen> createState() => _DesktopMainScreenState();
}

class _DesktopMainScreenState extends State<DesktopMainScreen> {
  int _selectedIndex = 0;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Row(
        children: [
          // Navigation rail
          NavigationRail(
            selectedIndex: _selectedIndex,
            onDestinationSelected: (index) {
              setState(() => _selectedIndex = index);
            },
            labelType: NavigationRailLabelType.all,
            destinations: const [
              NavigationRailDestination(
                icon: Icon(Icons.dashboard_outlined),
                selectedIcon: Icon(Icons.dashboard),
                label: Text('Dashboard'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.receipt_outlined),
                selectedIcon: Icon(Icons.receipt),
                label: Text('Facturen'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.account_balance_wallet_outlined),
                selectedIcon: Icon(Icons.account_balance_wallet),
                label: Text('Uitgaven'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.analytics_outlined),
                selectedIcon: Icon(Icons.analytics),
                label: Text('Rapporten'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.settings_outlined),
                selectedIcon: Icon(Icons.settings),
                label: Text('Instellingen'),
              ),
            ],
          ),

          const VerticalDivider(thickness: 1, width: 1),

          // Main content
          Expanded(
            child: _screens[_selectedIndex],
          ),
        ],
      ),
    );
  }
}
```

### 3. Navigation Drawer (Optional)

**For secondary navigation**:
```dart
Drawer(
  child: ListView(
    padding: EdgeInsets.zero,
    children: [
      UserAccountsDrawerHeader(
        accountName: Text(user.name),
        accountEmail: Text(user.email),
        currentAccountPicture: CircleAvatar(
          child: Text(user.initials),
        ),
      ),
      ListTile(
        leading: Icon(Icons.business),
        title: Text('Bedrijven'),
        onTap: () => Navigator.pushNamed(context, '/companies'),
      ),
      ListTile(
        leading: Icon(Icons.people),
        title: Text('Klanten'),
        onTap: () => Navigator.pushNamed(context, '/clients'),
      ),
      Divider(),
      ListTile(
        leading: Icon(Icons.help),
        title: Text('Help'),
        onTap: () => Navigator.pushNamed(context, '/help'),
      ),
      ListTile(
        leading: Icon(Icons.logout),
        title: Text('Uitloggen'),
        onTap: () => logout(),
      ),
    ],
  ),
)
```

## Material Design 3 (Android)

### 1. Color Scheme

**Define app colors**:
```dart
final colorScheme = ColorScheme.fromSeed(
  seedColor: Colors.blue,
  brightness: Brightness.light,
);

final darkColorScheme = ColorScheme.fromSeed(
  seedColor: Colors.blue,
  brightness: Brightness.dark,
);

MaterialApp(
  theme: ThemeData(
    colorScheme: colorScheme,
    useMaterial3: true,
  ),
  darkTheme: ThemeData(
    colorScheme: darkColorScheme,
    useMaterial3: true,
  ),
  themeMode: ThemeMode.system,
)
```

### 2. Cards and Surfaces

**Material 3 card styles**:
```dart
Card(
  elevation: 0,
  shape: RoundedRectangleBorder(
    borderRadius: BorderRadius.circular(12),
    side: BorderSide(
      color: Theme.of(context).colorScheme.outline,
    ),
  ),
  child: Padding(
    padding: EdgeInsets.all(16),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Factuurnummer',
          style: Theme.of(context).textTheme.labelMedium,
        ),
        SizedBox(height: 8),
        Text(
          'INV-2025-001',
          style: Theme.of(context).textTheme.headlineSmall,
        ),
      ],
    ),
  ),
)
```

### 3. Floating Action Button

**Primary action button**:
```dart
Scaffold(
  floatingActionButton: FloatingActionButton.extended(
    onPressed: () => createInvoice(),
    icon: Icon(Icons.add),
    label: Text('Nieuwe factuur'),
  ),
)
```

### 4. Bottom Sheets

**Modal bottom sheets for actions**:
```dart
void showInvoiceOptions(BuildContext context, Invoice invoice) {
  showModalBottomSheet(
    context: context,
    builder: (context) => Container(
      padding: EdgeInsets.symmetric(vertical: 16),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          ListTile(
            leading: Icon(Icons.edit),
            title: Text('Bewerken'),
            onTap: () {
              Navigator.pop(context);
              editInvoice(invoice);
            },
          ),
          ListTile(
            leading: Icon(Icons.send),
            title: Text('Verzenden'),
            onTap: () {
              Navigator.pop(context);
              sendInvoice(invoice);
            },
          ),
          ListTile(
            leading: Icon(Icons.download),
            title: Text('Downloaden'),
            onTap: () {
              Navigator.pop(context);
              downloadInvoice(invoice);
            },
          ),
          ListTile(
            leading: Icon(Icons.delete, color: Colors.red),
            title: Text('Verwijderen', style: TextStyle(color: Colors.red)),
            onTap: () {
              Navigator.pop(context);
              deleteInvoice(invoice);
            },
          ),
        ],
      ),
    ),
  );
}
```

## Cupertino Design (iOS)

### 1. Navigation Bar

**iOS-style top navigation**:
```dart
CupertinoPageScaffold(
  navigationBar: CupertinoNavigationBar(
    middle: Text('Facturen'),
    trailing: CupertinoButton(
      padding: EdgeInsets.zero,
      child: Icon(CupertinoIcons.add),
      onPressed: () => createInvoice(),
    ),
  ),
  child: SafeArea(
    child: InvoiceList(),
  ),
)
```

### 2. Action Sheets

**iOS-style action menus**:
```dart
void showInvoiceOptions(BuildContext context, Invoice invoice) {
  showCupertinoModalPopup(
    context: context,
    builder: (context) => CupertinoActionSheet(
      title: Text('Factuur opties'),
      message: Text(invoice.number),
      actions: [
        CupertinoActionSheetAction(
          onPressed: () {
            Navigator.pop(context);
            editInvoice(invoice);
          },
          child: Text('Bewerken'),
        ),
        CupertinoActionSheetAction(
          onPressed: () {
            Navigator.pop(context);
            sendInvoice(invoice);
          },
          child: Text('Verzenden'),
        ),
        CupertinoActionSheetAction(
          onPressed: () {
            Navigator.pop(context);
            downloadInvoice(invoice);
          },
          child: Text('Downloaden'),
        ),
        CupertinoActionSheetAction(
          onPressed: () {
            Navigator.pop(context);
            deleteInvoice(invoice);
          },
          isDestructiveAction: true,
          child: Text('Verwijderen'),
        ),
      ],
      cancelButton: CupertinoActionSheetAction(
        onPressed: () => Navigator.pop(context),
        child: Text('Annuleren'),
      ),
    ),
  );
}
```

### 3. Segmented Control

**iOS-style tabs**:
```dart
CupertinoSegmentedControl<int>(
  groupValue: _selectedSegment,
  onValueChanged: (value) {
    setState(() => _selectedSegment = value);
  },
  children: {
    0: Padding(
      padding: EdgeInsets.symmetric(horizontal: 20),
      child: Text('Alle'),
    ),
    1: Padding(
      padding: EdgeInsets.symmetric(horizontal: 20),
      child: Text('Verzonden'),
    ),
    2: Padding(
      padding: EdgeInsets.symmetric(horizontal: 20),
      child: Text('Betaald'),
    ),
  },
)
```

## Windows Desktop Design

### 1. Title Bar

**Custom title bar with controls**:
```dart
import 'package:bitsdojo_window/bitsdojo_window.dart';

class WindowsTitleBar extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return WindowTitleBarBox(
      child: Row(
        children: [
          Expanded(
            child: MoveWindow(
              child: Container(
                padding: EdgeInsets.symmetric(horizontal: 16),
                child: Row(
                  children: [
                    Image.asset('assets/icon.png', height: 20),
                    SizedBox(width: 8),
                    Text(
                      'Boekhouder',
                      style: TextStyle(fontSize: 12),
                    ),
                  ],
                ),
              ),
            ),
          ),
          WindowButtons(),
        ],
      ),
    );
  }
}

class WindowButtons extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        MinimizeWindowButton(),
        MaximizeWindowButton(),
        CloseWindowButton(),
      ],
    );
  }
}
```

### 2. Keyboard Shortcuts

**Support keyboard navigation**:
```dart
import 'package:flutter/services.dart';

class InvoiceScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Shortcuts(
      shortcuts: {
        LogicalKeySet(LogicalKeyboardKey.control, LogicalKeyboardKey.keyN):
            CreateInvoiceIntent(),
        LogicalKeySet(LogicalKeyboardKey.control, LogicalKeyboardKey.keyS):
            SaveIntent(),
        LogicalKeySet(LogicalKeyboardKey.control, LogicalKeyboardKey.keyP):
            PrintIntent(),
      },
      child: Actions(
        actions: {
          CreateInvoiceIntent: CallbackAction<CreateInvoiceIntent>(
            onInvoke: (_) => createInvoice(),
          ),
          SaveIntent: CallbackAction<SaveIntent>(
            onInvoke: (_) => save(),
          ),
          PrintIntent: CallbackAction<PrintIntent>(
            onInvoke: (_) => print(),
          ),
        },
        child: Focus(
          autofocus: true,
          child: InvoiceForm(),
        ),
      ),
    );
  }
}

class CreateInvoiceIntent extends Intent {}
```

## Linux Desktop Design

### 1. GTK-Style Header Bar

**GNOME-style header bar with controls**:
```dart
import 'package:gtk/gtk.dart'; // Or use custom implementation

class LinuxHeaderBar extends StatelessWidget {
  final String title;
  final List<Widget>? actions;

  const LinuxHeaderBar({
    required this.title,
    this.actions,
    Key? key,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 48,
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        border: Border(
          bottom: BorderSide(
            color: Theme.of(context).colorScheme.outline.withOpacity(0.2),
          ),
        ),
      ),
      child: Row(
        children: [
          const SizedBox(width: 12),
          // App icon
          Image.asset('assets/icon.png', height: 24),
          const SizedBox(width: 12),
          // Title
          Text(
            title,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.w600,
            ),
          ),
          const Spacer(),
          // Actions
          if (actions != null) ...actions!,
          const SizedBox(width: 8),
        ],
      ),
    );
  }
}
```

### 2. Sidebar Navigation (GNOME-style)

**Linux-native sidebar pattern**:
```dart
class LinuxSidebar extends StatelessWidget {
  final int selectedIndex;
  final ValueChanged<int> onDestinationSelected;

  const LinuxSidebar({
    required this.selectedIndex,
    required this.onDestinationSelected,
    Key? key,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 240,
      color: Theme.of(context).colorScheme.surfaceVariant.withOpacity(0.3),
      child: Column(
        children: [
          const SizedBox(height: 12),
          _buildNavItem(context, 0, Icons.dashboard, 'Dashboard'),
          _buildNavItem(context, 1, Icons.receipt, 'Facturen'),
          _buildNavItem(context, 2, Icons.account_balance_wallet, 'Uitgaven'),
          _buildNavItem(context, 3, Icons.analytics, 'Rapporten'),
          const Spacer(),
          const Divider(),
          _buildNavItem(context, 4, Icons.settings, 'Instellingen'),
          const SizedBox(height: 12),
        ],
      ),
    );
  }

  Widget _buildNavItem(BuildContext context, int index, IconData icon, String label) {
    final isSelected = selectedIndex == index;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      child: Material(
        color: isSelected
            ? Theme.of(context).colorScheme.primaryContainer
            : Colors.transparent,
        borderRadius: BorderRadius.circular(8),
        child: InkWell(
          borderRadius: BorderRadius.circular(8),
          onTap: () => onDestinationSelected(index),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            child: Row(
              children: [
                Icon(
                  icon,
                  size: 20,
                  color: isSelected
                      ? Theme.of(context).colorScheme.onPrimaryContainer
                      : Theme.of(context).colorScheme.onSurfaceVariant,
                ),
                const SizedBox(width: 12),
                Text(
                  label,
                  style: TextStyle(
                    color: isSelected
                        ? Theme.of(context).colorScheme.onPrimaryContainer
                        : Theme.of(context).colorScheme.onSurfaceVariant,
                    fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
```

### 3. Linux Main Layout

**Complete Linux desktop layout**:
```dart
class LinuxMainScreen extends StatefulWidget {
  @override
  State<LinuxMainScreen> createState() => _LinuxMainScreenState();
}

class _LinuxMainScreenState extends State<LinuxMainScreen> {
  int _selectedIndex = 0;

  final List<Widget> _screens = [
    DashboardScreen(),
    InvoicesScreen(),
    ExpensesScreen(),
    ReportsScreen(),
    SettingsScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          // Header bar
          LinuxHeaderBar(
            title: 'Boekhouder',
            actions: [
              IconButton(
                icon: const Icon(Icons.search),
                onPressed: () {},
                tooltip: 'Zoeken (Ctrl+K)',
              ),
              IconButton(
                icon: const Icon(Icons.notifications_outlined),
                onPressed: () {},
                tooltip: 'Meldingen',
              ),
            ],
          ),
          // Main content with sidebar
          Expanded(
            child: Row(
              children: [
                LinuxSidebar(
                  selectedIndex: _selectedIndex,
                  onDestinationSelected: (index) {
                    setState(() => _selectedIndex = index);
                  },
                ),
                // Content area
                Expanded(
                  child: Container(
                    color: Theme.of(context).colorScheme.surface,
                    child: _screens[_selectedIndex],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
```

### 4. Linux-Specific Configuration

**pubspec.yaml for Linux support**:
```yaml
flutter:
  # Linux desktop support
  linux:
    generate: true

dependencies:
  # For GTK integration (optional)
  gtk: ^2.0.0

  # For system tray
  system_tray: ^2.0.0

  # For desktop notifications
  desktop_notifications: ^0.1.0
```

**Linux runner configuration** (linux/my_application.cc):
```cpp
// Set minimum window size
gtk_window_set_default_size(window, 1200, 800);
gtk_window_set_resizable(window, TRUE);

// Set window title
gtk_window_set_title(window, "Boekhouder");
```

### 3. Context Menus

**Right-click menus**:
```dart
GestureDetector(
  onSecondaryTapDown: (details) {
    showMenu(
      context: context,
      position: RelativeRect.fromLTRB(
        details.globalPosition.dx,
        details.globalPosition.dy,
        details.globalPosition.dx,
        details.globalPosition.dy,
      ),
      items: [
        PopupMenuItem(
          child: Text('Bewerken'),
          onTap: () => editInvoice(invoice),
        ),
        PopupMenuItem(
          child: Text('Kopiëren'),
          onTap: () => copyInvoice(invoice),
        ),
        PopupMenuDivider(),
        PopupMenuItem(
          child: Text('Verwijderen'),
          onTap: () => deleteInvoice(invoice),
        ),
      ],
    );
  },
  child: InvoiceListTile(invoice: invoice),
)
```

## Common UI Components

### 1. Data Table

**Responsive data table**:
```dart
class InvoiceTable extends StatelessWidget {
  final List<Invoice> invoices;

  const InvoiceTable({required this.invoices});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: DataTable(
        columns: const [
          DataColumn(label: Text('Nummer')),
          DataColumn(label: Text('Klant')),
          DataColumn(label: Text('Datum')),
          DataColumn(label: Text('Bedrag'), numeric: true),
          DataColumn(label: Text('Status')),
          DataColumn(label: Text('Acties')),
        ],
        rows: invoices.map((invoice) {
          return DataRow(
            cells: [
              DataCell(Text(invoice.number)),
              DataCell(Text(invoice.clientName)),
              DataCell(Text(formatDate(invoice.date))),
              DataCell(Text(formatCurrency(invoice.total))),
              DataCell(StatusChip(status: invoice.status)),
              DataCell(
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    IconButton(
                      icon: Icon(Icons.edit, size: 18),
                      onPressed: () => editInvoice(invoice),
                    ),
                    IconButton(
                      icon: Icon(Icons.send, size: 18),
                      onPressed: () => sendInvoice(invoice),
                    ),
                  ],
                ),
              ),
            ],
          );
        }).toList(),
      ),
    );
  }
}
```

### 2. Search Bar

**Platform-adaptive search**:
```dart
class PlatformSearchBar extends StatelessWidget {
  final String hintText;
  final ValueChanged<String> onChanged;

  const PlatformSearchBar({
    required this.hintText,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    if (Platform.isIOS) {
      return CupertinoSearchTextField(
        placeholder: hintText,
        onChanged: onChanged,
      );
    }

    return SearchBar(
      hintText: hintText,
      onChanged: onChanged,
      leading: Icon(Icons.search),
    );
  }
}
```

### 3. Loading Indicators

**Platform-specific loaders**:
```dart
class PlatformLoadingIndicator extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    if (Platform.isIOS) {
      return CupertinoActivityIndicator();
    }

    return CircularProgressIndicator();
  }
}
```

### 4. Status Chips

**Visual status indicators**:
```dart
class StatusChip extends StatelessWidget {
  final InvoiceStatus status;

  const StatusChip({required this.status});

  Color _getColor() {
    switch (status) {
      case InvoiceStatus.draft:
        return Colors.grey;
      case InvoiceStatus.sent:
        return Colors.blue;
      case InvoiceStatus.paid:
        return Colors.green;
      case InvoiceStatus.overdue:
        return Colors.red;
      case InvoiceStatus.cancelled:
        return Colors.grey;
    }
  }

  String _getLabel() {
    switch (status) {
      case InvoiceStatus.draft:
        return 'Concept';
      case InvoiceStatus.sent:
        return 'Verzonden';
      case InvoiceStatus.paid:
        return 'Betaald';
      case InvoiceStatus.overdue:
        return 'Verlopen';
      case InvoiceStatus.cancelled:
        return 'Geannuleerd';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Chip(
      label: Text(
        _getLabel(),
        style: TextStyle(
          color: Colors.white,
          fontSize: 12,
        ),
      ),
      backgroundColor: _getColor(),
      padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
    );
  }
}
```

## Forms and Input

### 1. Form Validation

**Proper form handling**:
```dart
class InvoiceForm extends StatefulWidget {
  @override
  State<InvoiceForm> createState() => _InvoiceFormState();
}

class _InvoiceFormState extends State<InvoiceForm> {
  final _formKey = GlobalKey<FormState>();
  final _clientController = TextEditingController();
  final _amountController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return Form(
      key: _formKey,
      child: Column(
        children: [
          TextFormField(
            controller: _clientController,
            decoration: InputDecoration(
              labelText: 'Klant',
              hintText: 'Selecteer een klant',
            ),
            validator: (value) {
              if (value == null || value.isEmpty) {
                return 'Selecteer een klant';
              }
              return null;
            },
          ),

          SizedBox(height: 16),

          TextFormField(
            controller: _amountController,
            decoration: InputDecoration(
              labelText: 'Bedrag',
              prefixText: '€ ',
            ),
            keyboardType: TextInputType.numberWithOptions(decimal: true),
            validator: (value) {
              if (value == null || value.isEmpty) {
                return 'Voer een bedrag in';
              }
              final amount = double.tryParse(value);
              if (amount == null || amount <= 0) {
                return 'Voer een geldig bedrag in';
              }
              return null;
            },
          ),

          SizedBox(height: 24),

          ElevatedButton(
            onPressed: () {
              if (_formKey.currentState!.validate()) {
                // Process data
                saveInvoice();
              }
            },
            child: Text('Opslaan'),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _clientController.dispose();
    _amountController.dispose();
    super.dispose();
  }
}
```

### 2. Date Picker

**Platform-adaptive date selection**:
```dart
Future<DateTime?> selectDate(BuildContext context, DateTime initialDate) async {
  if (Platform.isIOS) {
    DateTime? selectedDate;

    await showCupertinoModalPopup(
      context: context,
      builder: (context) => Container(
        height: 300,
        color: CupertinoColors.white,
        child: Column(
          children: [
            Container(
              height: 50,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  CupertinoButton(
                    child: Text('Annuleren'),
                    onPressed: () => Navigator.pop(context),
                  ),
                  CupertinoButton(
                    child: Text('Klaar'),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
            ),
            Expanded(
              child: CupertinoDatePicker(
                mode: CupertinoDatePickerMode.date,
                initialDateTime: initialDate,
                onDateTimeChanged: (date) {
                  selectedDate = date;
                },
              ),
            ),
          ],
        ),
      ),
    );

    return selectedDate;
  }

  return showDatePicker(
    context: context,
    initialDate: initialDate,
    firstDate: DateTime(2000),
    lastDate: DateTime(2100),
  );
}
```

## State Management

### 1. Provider Pattern (Recommended)

**Use Provider for state**:
```dart
import 'package:provider/provider.dart';

class InvoiceProvider extends ChangeNotifier {
  List<Invoice> _invoices = [];
  bool _isLoading = false;

  List<Invoice> get invoices => _invoices;
  bool get isLoading => _isLoading;

  Future<void> loadInvoices() async {
    _isLoading = true;
    notifyListeners();

    try {
      _invoices = await apiService.fetchInvoices();
    } catch (e) {
      // Handle error
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> createInvoice(Invoice invoice) async {
    await apiService.createInvoice(invoice);
    _invoices.add(invoice);
    notifyListeners();
  }
}

// Usage in main.dart:
MultiProvider(
  providers: [
    ChangeNotifierProvider(create: (_) => InvoiceProvider()),
    ChangeNotifierProvider(create: (_) => ExpenseProvider()),
  ],
  child: MyApp(),
)

// Usage in widget:
class InvoiceList extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Consumer<InvoiceProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) {
          return PlatformLoadingIndicator();
        }

        return ListView.builder(
          itemCount: provider.invoices.length,
          itemBuilder: (context, index) {
            return InvoiceListTile(invoice: provider.invoices[index]);
          },
        );
      },
    );
  }
}
```

## Performance Optimization

### 1. Lazy Loading Lists

**Efficient list rendering**:
```dart
ListView.builder(
  itemCount: invoices.length,
  itemBuilder: (context, index) {
    return InvoiceListTile(invoice: invoices[index]);
  },
)

// Or for complex items:
ListView.separated(
  itemCount: invoices.length,
  separatorBuilder: (context, index) => Divider(),
  itemBuilder: (context, index) {
    return InvoiceListTile(invoice: invoices[index]);
  },
)
```

### 2. Image Caching

**Cache network images**:
```dart
import 'package:cached_network_image/cached_network_image.dart';

CachedNetworkImage(
  imageUrl: companyLogo,
  placeholder: (context, url) => CircularProgressIndicator(),
  errorWidget: (context, url, error) => Icon(Icons.error),
  width: 100,
  height: 100,
)
```

### 3. Const Constructors

**Use const for performance**:
```dart
// Good - const constructor
const Text('Hello')

const Icon(Icons.add)

const SizedBox(height: 16)

// Bad - non-const
Text('Hello')  // creates new instance every build
```

## Accessibility

### 1. Semantic Labels

**Screen reader support**:
```dart
Semantics(
  label: 'Nieuwe factuur aanmaken',
  child: FloatingActionButton(
    onPressed: createInvoice,
    child: Icon(Icons.add),
  ),
)

// Exclude decorative elements
ExcludeSemantics(
  child: Container(
    decoration: BoxDecoration(/* decorative only */),
  ),
)
```

### 2. Text Scaling

**Support system text size**:
```dart
// This automatically scales with system settings:
Text(
  'Factuur',
  style: Theme.of(context).textTheme.headlineMedium,
)

// Limit scaling if needed:
Text(
  'Factuur',
  textScaleFactor: 1.0,  // Fixed size
)
```

## Testing

### 1. Widget Tests

**Test UI components**:
```dart
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('InvoiceListTile shows invoice data', (tester) async {
    final invoice = Invoice(
      number: 'INV-001',
      clientName: 'Test Client',
      total: 100.0,
      status: InvoiceStatus.paid,
    );

    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: InvoiceListTile(invoice: invoice),
        ),
      ),
    );

    expect(find.text('INV-001'), findsOneWidget);
    expect(find.text('Test Client'), findsOneWidget);
    expect(find.text('€ 100,00'), findsOneWidget);
    expect(find.text('Betaald'), findsOneWidget);
  });
}
```

## Common Issues

### Issue 1: Overflow Errors

**Problem**: Text or widgets overflow screen

**Solution**:
```dart
// Wrap in SingleChildScrollView
SingleChildScrollView(
  child: Column(
    children: [
      // Long content
    ],
  ),
)

// Or use Expanded/Flexible
Column(
  children: [
    Expanded(
      child: ListView(...),
    ),
  ],
)
```

### Issue 2: Platform Detection

**Problem**: Need to detect platform at runtime

**Solution**:
```dart
import 'dart:io' show Platform;
import 'package:flutter/foundation.dart' show kIsWeb;

if (kIsWeb) {
  // Web platform
} else if (Platform.isAndroid) {
  // Android
} else if (Platform.isIOS) {
  // iOS
} else if (Platform.isWindows) {
  // Windows
}
```

### Issue 3: Keyboard Overlap

**Problem**: Keyboard covers input fields

**Solution**:
```dart
Scaffold(
  resizeToAvoidBottomInset: true,  // Default, but ensure it's set
  body: SingleChildScrollView(
    padding: EdgeInsets.only(
      bottom: MediaQuery.of(context).viewInsets.bottom,
    ),
    child: Form(...),
  ),
)
```

## Dutch Bookkeeping-Specific Examples

### Example 1: VAT Rate Selector with Dutch Rates

```dart
import 'package:flutter/material.dart';

class VatRateSelector extends StatefulWidget {
  final double? initialRate;
  final ValueChanged<double> onRateChanged;

  const VatRateSelector({
    this.initialRate,
    required this.onRateChanged,
    Key? key,
  }) : super(key: key);

  @override
  State<VatRateSelector> createState() => _VatRateSelectorState();
}

class _VatRateSelectorState extends State<VatRateSelector> {
  static const Map<String, double> dutchVatRates = {
    'Hoog (21%)': 0.21,
    'Laag (9%)': 0.09,
    'Nul (0%)': 0.00,
    'Verlegd': -1.0, // Reverse charge
  };

  double? _selectedRate;

  @override
  void initState() {
    super.initState();
    _selectedRate = widget.initialRate;
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(
          color: Theme.of(context).colorScheme.outline,
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'BTW-tarief',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 12),
            ...dutchVatRates.entries.map((entry) {
              final isSelected = _selectedRate == entry.value;
              return RadioListTile<double>(
                value: entry.value,
                groupValue: _selectedRate,
                title: Text(entry.key),
                subtitle: entry.value == -1.0
                    ? const Text('Verlegging van BTW')
                    : null,
                selected: isSelected,
                contentPadding: EdgeInsets.zero,
                onChanged: (value) {
                  setState(() {
                    _selectedRate = value;
                    if (value != null) {
                      widget.onRateChanged(value);
                    }
                  });
                },
              );
            }).toList(),
          ],
        ),
      ),
    );
  }
}

// Usage:
VatRateSelector(
  initialRate: 0.21,
  onRateChanged: (rate) {
    print('Selected VAT rate: ${rate * 100}%');
    // Update invoice line item
  },
)
```

### Example 2: Invoice Entry Form with Dutch Fields

```dart
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class DutchInvoiceEntryForm extends StatefulWidget {
  @override
  State<DutchInvoiceEntryForm> createState() => _DutchInvoiceEntryFormState();
}

class _DutchInvoiceEntryFormState extends State<DutchInvoiceEntryForm> {
  final _formKey = GlobalKey<FormState>();
  final _invoiceNumberController = TextEditingController();
  final _clientNameController = TextEditingController();
  final _kvkController = TextEditingController();
  final _btwController = TextEditingController();
  final _amountController = TextEditingController();

  DateTime _invoiceDate = DateTime.now();
  DateTime _dueDate = DateTime.now().add(const Duration(days: 30));

  final _dutchDateFormat = DateFormat('dd-MM-yyyy', 'nl_NL');
  final _dutchCurrencyFormat = NumberFormat.currency(
    locale: 'nl_NL',
    symbol: '€',
    decimalDigits: 2,
  );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Nieuwe Factuur'),
        actions: [
          IconButton(
            icon: const Icon(Icons.help_outline),
            onPressed: () {
              // Show help
            },
          ),
        ],
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16.0),
          children: [
            // Invoice Number
            TextFormField(
              controller: _invoiceNumberController,
              decoration: const InputDecoration(
                labelText: 'Factuurnummer',
                hintText: 'bijv. 2025-001',
                prefixIcon: Icon(Icons.tag),
                border: OutlineInputBorder(),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Factuurnummer is verplicht';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),

            // Client Name
            TextFormField(
              controller: _clientNameController,
              decoration: const InputDecoration(
                labelText: 'Klantnaam',
                hintText: 'Naam van de klant of bedrijf',
                prefixIcon: Icon(Icons.business),
                border: OutlineInputBorder(),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Klantnaam is verplicht';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),

            // KVK Number
            TextFormField(
              controller: _kvkController,
              decoration: const InputDecoration(
                labelText: 'KVK-nummer',
                hintText: '12345678',
                prefixIcon: Icon(Icons.account_balance),
                border: OutlineInputBorder(),
                helperText: '8 cijfers',
              ),
              keyboardType: TextInputType.number,
              maxLength: 8,
              validator: (value) {
                if (value != null && value.isNotEmpty) {
                  if (!RegExp(r'^\d{8}$').hasMatch(value)) {
                    return 'KVK-nummer moet 8 cijfers zijn';
                  }
                }
                return null;
              },
            ),
            const SizedBox(height: 16),

            // BTW Number
            TextFormField(
              controller: _btwController,
              decoration: const InputDecoration(
                labelText: 'BTW-nummer',
                hintText: 'NL123456789B01',
                prefixIcon: Icon(Icons.receipt_long),
                border: OutlineInputBorder(),
                helperText: 'Format: NL000000000B00',
              ),
              validator: (value) {
                if (value != null && value.isNotEmpty) {
                  if (!RegExp(r'^NL\d{9}B\d{2}$').hasMatch(value)) {
                    return 'Ongeldig BTW-nummer format';
                  }
                }
                return null;
              },
            ),
            const SizedBox(height: 16),

            // Invoice Date
            ListTile(
              contentPadding: EdgeInsets.zero,
              leading: const Icon(Icons.calendar_today),
              title: const Text('Factuurdatum'),
              subtitle: Text(_dutchDateFormat.format(_invoiceDate)),
              trailing: const Icon(Icons.edit),
              onTap: () async {
                final date = await showDatePicker(
                  context: context,
                  initialDate: _invoiceDate,
                  firstDate: DateTime(2000),
                  lastDate: DateTime(2100),
                  locale: const Locale('nl', 'NL'),
                );
                if (date != null) {
                  setState(() => _invoiceDate = date);
                }
              },
            ),
            const Divider(),

            // Due Date
            ListTile(
              contentPadding: EdgeInsets.zero,
              leading: const Icon(Icons.event),
              title: const Text('Vervaldatum'),
              subtitle: Text(_dutchDateFormat.format(_dueDate)),
              trailing: const Icon(Icons.edit),
              onTap: () async {
                final date = await showDatePicker(
                  context: context,
                  initialDate: _dueDate,
                  firstDate: _invoiceDate,
                  lastDate: DateTime(2100),
                  locale: const Locale('nl', 'NL'),
                );
                if (date != null) {
                  setState(() => _dueDate = date);
                }
              },
            ),
            const Divider(),

            // Amount
            TextFormField(
              controller: _amountController,
              decoration: const InputDecoration(
                labelText: 'Bedrag (excl. BTW)',
                hintText: '0,00',
                prefixText: '€ ',
                prefixIcon: Icon(Icons.euro),
                border: OutlineInputBorder(),
              ),
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Bedrag is verplicht';
                }
                final amount = double.tryParse(value.replaceAll(',', '.'));
                if (amount == null || amount <= 0) {
                  return 'Voer een geldig bedrag in';
                }
                return null;
              },
            ),
            const SizedBox(height: 24),

            // Save Button
            FilledButton.icon(
              onPressed: () {
                if (_formKey.currentState!.validate()) {
                  // Save invoice
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Factuur opgeslagen'),
                      behavior: SnackBarBehavior.floating,
                    ),
                  );
                }
              },
              icon: const Icon(Icons.save),
              label: const Text('Factuur Opslaan'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    _invoiceNumberController.dispose();
    _clientNameController.dispose();
    _kvkController.dispose();
    _btwController.dispose();
    _amountController.dispose();
    super.dispose();
  }
}
```

### Example 3: Financial Dashboard with Charts

```dart
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class FinancialDashboard extends StatelessWidget {
  final _currencyFormat = NumberFormat.currency(
    locale: 'nl_NL',
    symbol: '€',
    decimalDigits: 2,
  );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard'),
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: () {
              // Show filter options
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          // Refresh data
          await Future.delayed(const Duration(seconds: 1));
        },
        child: ListView(
          padding: const EdgeInsets.all(16.0),
          children: [
            // Financial Overview Cards
            Row(
              children: [
                Expanded(
                  child: _buildFinancialCard(
                    context,
                    title: 'Omzet deze maand',
                    amount: 45250.00,
                    icon: Icons.trending_up,
                    color: Colors.green,
                    trend: '+12%',
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildFinancialCard(
                    context,
                    title: 'Uitgaven',
                    amount: 12340.00,
                    icon: Icons.trending_down,
                    color: Colors.red,
                    trend: '-5%',
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            Row(
              children: [
                Expanded(
                  child: _buildFinancialCard(
                    context,
                    title: 'Openstaand',
                    amount: 8750.00,
                    icon: Icons.schedule,
                    color: Colors.orange,
                    trend: '3 facturen',
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildFinancialCard(
                    context,
                    title: 'BTW te betalen',
                    amount: 2205.50,
                    icon: Icons.receipt_long,
                    color: Colors.blue,
                    trend: 'Q1 2025',
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Quick Actions
            Text(
              'Snelle Acties',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 12),
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 1.5,
              children: [
                _buildActionCard(
                  context,
                  title: 'Nieuwe Factuur',
                  icon: Icons.add_circle_outline,
                  color: Colors.blue,
                  onTap: () {
                    // Navigate to new invoice
                  },
                ),
                _buildActionCard(
                  context,
                  title: 'Uitgave Toevoegen',
                  icon: Icons.receipt,
                  color: Colors.orange,
                  onTap: () {
                    // Navigate to new expense
                  },
                ),
                _buildActionCard(
                  context,
                  title: 'BTW-Aangifte',
                  icon: Icons.description,
                  color: Colors.purple,
                  onTap: () {
                    // Navigate to VAT declaration
                  },
                ),
                _buildActionCard(
                  context,
                  title: 'Rapporten',
                  icon: Icons.analytics,
                  color: Colors.green,
                  onTap: () {
                    // Navigate to reports
                  },
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Recent Activity
            Text(
              'Recente Activiteit',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 12),
            Card(
              child: Column(
                children: [
                  _buildActivityItem(
                    context,
                    icon: Icons.check_circle,
                    iconColor: Colors.green,
                    title: 'Factuur INV-2025-042 betaald',
                    subtitle: '€ 1.250,00 • 2 uur geleden',
                  ),
                  const Divider(height: 1),
                  _buildActivityItem(
                    context,
                    icon: Icons.send,
                    iconColor: Colors.blue,
                    title: 'Factuur INV-2025-043 verzonden',
                    subtitle: 'Klant: ABC Bedrijf B.V. • 5 uur geleden',
                  ),
                  const Divider(height: 1),
                  _buildActivityItem(
                    context,
                    icon: Icons.warning,
                    iconColor: Colors.orange,
                    title: 'Factuur INV-2025-035 bijna verlopen',
                    subtitle: 'Vervalt over 3 dagen',
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFinancialCard(
    BuildContext context, {
    required String title,
    required double amount,
    required IconData icon,
    required Color color,
    required String trend,
  }) {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(
          color: Theme.of(context).colorScheme.outline.withOpacity(0.5),
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, color: color, size: 20),
                const Spacer(),
                Text(
                  trend,
                  style: TextStyle(
                    color: color,
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              title,
              style: Theme.of(context).textTheme.bodySmall,
            ),
            const SizedBox(height: 4),
            Text(
              _currencyFormat.format(amount),
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionCard(
    BuildContext context, {
    required String title,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(
          color: Theme.of(context).colorScheme.outline.withOpacity(0.5),
        ),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, color: color, size: 32),
              const SizedBox(height: 8),
              Text(
                title,
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.titleSmall,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildActivityItem(
    BuildContext context, {
    required IconData icon,
    required Color iconColor,
    required String title,
    required String subtitle,
  }) {
    return ListTile(
      leading: Icon(icon, color: iconColor),
      title: Text(title),
      subtitle: Text(subtitle),
      dense: true,
    );
  }
}
```

## Troubleshooting

### Problem 1: Dutch Date Format Not Displaying Correctly

**Symptom**: Dates show in US format (MM/dd/yyyy) instead of Dutch format (dd-MM-yyyy)

**Solution**:
```dart
// Add intl package to pubspec.yaml
// dependencies:
//   intl: ^0.18.0

import 'package:intl/intl.dart';
import 'package:intl/date_symbol_data_local.dart';

// Initialize Dutch locale in main()
void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await initializeDateFormatting('nl_NL', null);
  runApp(MyApp());
}

// Use Dutch date formatter
final dutchDateFormat = DateFormat('dd-MM-yyyy', 'nl_NL');
final dutchDateTimeFormat = DateFormat('dd-MM-yyyy HH:mm', 'nl_NL');

// Format dates
Text(dutchDateFormat.format(DateTime.now())); // "13-12-2025"

// Configure DatePicker with Dutch locale
showDatePicker(
  context: context,
  initialDate: DateTime.now(),
  firstDate: DateTime(2000),
  lastDate: DateTime(2100),
  locale: const Locale('nl', 'NL'), // Dutch locale
);
```

### Problem 2: Currency Symbol Not Rendering in PDFs

**Symptom**: Euro symbol (€) displays as � or empty box in generated PDFs

**Solution**:
```dart
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:flutter/services.dart' show rootBundle;

Future<void> generateInvoicePdf() async {
  final pdf = pw.Document();

  // Load font that supports Euro symbol
  final fontData = await rootBundle.load('assets/fonts/Roboto-Regular.ttf');
  final ttf = pw.Font.ttf(fontData);

  pdf.addPage(
    pw.Page(
      build: (context) => pw.Column(
        children: [
          pw.Text(
            'Totaal: € 1.250,00',
            style: pw.TextStyle(
              font: ttf, // Use font with Euro support
              fontSize: 16,
            ),
          ),
        ],
      ),
    ),
  );

  // Save PDF
}

// Alternative: Use escape code
pw.Text('Totaal: \u20AC 1.250,00'); // Unicode for €
```

### Problem 3: Number Formatting with Comma as Decimal Separator

**Symptom**: Numbers display with period (1.250,00) instead of comma as decimal separator

**Solution**:
```dart
import 'package:intl/intl.dart';

// Configure Dutch number formatter
final dutchCurrencyFormat = NumberFormat.currency(
  locale: 'nl_NL',
  symbol: '€',
  decimalDigits: 2,
);

final dutchNumberFormat = NumberFormat.decimalPattern('nl_NL');

// Format currency
Text(dutchCurrencyFormat.format(1250.00)); // "€ 1.250,00"

// Format numbers
Text(dutchNumberFormat.format(12345.67)); // "12.345,67"

// Parse user input (comma to period for calculation)
String parseUserInput(String input) {
  return input.replaceAll('.', '').replaceAll(',', '.');
}

double? amount = double.tryParse(parseUserInput('1.250,50')); // 1250.50
```

### Problem 4: Bottom Navigation Bar Covering FAB on Android

**Symptom**: Floating Action Button is partially or fully hidden by bottom navigation

**Solution**:
```dart
Scaffold(
  body: _screens[_currentIndex],
  floatingActionButton: FloatingActionButton.extended(
    onPressed: () => createInvoice(),
    icon: const Icon(Icons.add),
    label: const Text('Nieuwe factuur'),
  ),
  floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
  bottomNavigationBar: BottomAppBar(
    shape: const CircularNotchedRectangle(),
    notchMargin: 8.0,
    child: Row(
      mainAxisAlignment: MainAxisAlignment.spaceAround,
      children: [
        IconButton(
          icon: const Icon(Icons.dashboard),
          onPressed: () => _navigateTo(0),
        ),
        IconButton(
          icon: const Icon(Icons.receipt),
          onPressed: () => _navigateTo(1),
        ),
        const SizedBox(width: 48), // Space for FAB
        IconButton(
          icon: const Icon(Icons.analytics),
          onPressed: () => _navigateTo(2),
        ),
        IconButton(
          icon: const Icon(Icons.settings),
          onPressed: () => _navigateTo(3),
        ),
      ],
    ),
  ),
);

// Or use NavigationBar with padding
bottomNavigationBar: Padding(
  padding: const EdgeInsets.only(bottom: 16.0),
  child: NavigationBar(
    selectedIndex: _currentIndex,
    onDestinationSelected: (index) {
      setState(() => _currentIndex = index);
    },
    destinations: const [
      // ... destinations
    ],
  ),
),
```

### Problem 5: Platform Detection Fails on Web

**Symptom**: `Platform.isIOS` throws error on Flutter Web

**Solution**:
```dart
import 'dart:io' show Platform;
import 'package:flutter/foundation.dart' show kIsWeb;

// Safe platform detection
bool isIOS() {
  if (kIsWeb) return false;
  return Platform.isIOS;
}

bool isAndroid() {
  if (kIsWeb) return false;
  return Platform.isAndroid;
}

bool isDesktop() {
  if (kIsWeb) return false;
  return Platform.isWindows || Platform.isMacOS || Platform.isLinux;
}

// Usage in widgets
Widget build(BuildContext context) {
  if (kIsWeb) {
    return _buildWebLayout();
  } else if (isIOS()) {
    return _buildIOSLayout();
  } else if (isAndroid()) {
    return _buildAndroidLayout();
  } else {
    return _buildDesktopLayout();
  }
}
```

## Best Practices

### 1. Use Dutch Locale Throughout App

Always configure your app for the Dutch locale to ensure proper formatting:

```dart
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:intl/date_symbol_data_local.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await initializeDateFormatting('nl_NL', null);
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      // Dutch localization
      locale: const Locale('nl', 'NL'),
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      supportedLocales: const [
        Locale('nl', 'NL'),
        Locale('en', 'US'),
      ],
      theme: ThemeData(useMaterial3: true),
      home: HomePage(),
    );
  }
}
```

### 2. Implement Proper Currency Formatting

Use consistent currency formatting across all financial displays:

```dart
// Create reusable formatters
class AppFormatters {
  static final currency = NumberFormat.currency(
    locale: 'nl_NL',
    symbol: '€',
    decimalDigits: 2,
  );

  static final currencyCompact = NumberFormat.compactCurrency(
    locale: 'nl_NL',
    symbol: '€',
    decimalDigits: 0,
  );

  static final percentage = NumberFormat.percentPattern('nl_NL');

  static final date = DateFormat('dd-MM-yyyy', 'nl_NL');
  static final dateTime = DateFormat('dd-MM-yyyy HH:mm', 'nl_NL');
}

// Usage
Text(AppFormatters.currency.format(1250.00)); // € 1.250,00
Text(AppFormatters.currencyCompact.format(1250000)); // € 1,25 mln.
```

### 3. Design Responsive Layouts for All Screen Sizes

Support phones, tablets, and desktop with adaptive layouts:

```dart
class ResponsiveScaffold extends StatelessWidget {
  final Widget mobile;
  final Widget? tablet;
  final Widget? desktop;

  const ResponsiveScaffold({
    required this.mobile,
    this.tablet,
    this.desktop,
  });

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        // Desktop: > 1200px width
        if (constraints.maxWidth >= 1200) {
          return desktop ?? tablet ?? mobile;
        }
        // Tablet: 600-1199px width
        if (constraints.maxWidth >= 600) {
          return tablet ?? mobile;
        }
        // Mobile: < 600px width
        return mobile;
      },
    );
  }
}
```

### 4. Support Both Light and Dark Modes

Implement proper theming for accessibility:

```dart
MaterialApp(
  theme: ThemeData(
    colorScheme: ColorScheme.fromSeed(
      seedColor: Colors.blue,
      brightness: Brightness.light,
    ),
    useMaterial3: true,
  ),
  darkTheme: ThemeData(
    colorScheme: ColorScheme.fromSeed(
      seedColor: Colors.blue,
      brightness: Brightness.dark,
    ),
    useMaterial3: true,
  ),
  themeMode: ThemeMode.system, // Follow system preference
);

// Access theme colors properly
Container(
  color: Theme.of(context).colorScheme.surface,
  child: Text(
    'Content',
    style: TextStyle(
      color: Theme.of(context).colorScheme.onSurface,
    ),
  ),
)
```

### 5. Use Semantic Widgets for Accessibility

Make your app accessible to screen readers:

```dart
Semantics(
  label: 'Factuurbedrag: ${AppFormatters.currency.format(amount)}',
  child: Text(AppFormatters.currency.format(amount)),
)

Semantics(
  button: true,
  label: 'Nieuwe factuur aanmaken',
  child: FloatingActionButton(
    onPressed: createInvoice,
    child: const Icon(Icons.add),
  ),
)

// Exclude decorative elements
ExcludeSemantics(
  child: Container(
    decoration: BoxDecoration(/* decorative only */),
  ),
)
```

### 6. Handle Loading and Error States Gracefully

Always show appropriate feedback to users:

```dart
class InvoiceList extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<Invoice>>(
      future: fetchInvoices(),
      builder: (context, snapshot) {
        // Loading state
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                CircularProgressIndicator(),
                SizedBox(height: 16),
                Text('Facturen laden...'),
              ],
            ),
          );
        }

        // Error state
        if (snapshot.hasError) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.error_outline, size: 48, color: Colors.red),
                const SizedBox(height: 16),
                Text('Fout bij laden: ${snapshot.error}'),
                const SizedBox(height: 16),
                ElevatedButton.icon(
                  onPressed: () {
                    // Retry
                  },
                  icon: const Icon(Icons.refresh),
                  label: const Text('Opnieuw proberen'),
                ),
              ],
            ),
          );
        }

        // Empty state
        if (!snapshot.hasData || snapshot.data!.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.receipt_long_outlined, size: 64),
                const SizedBox(height: 16),
                const Text('Geen facturen gevonden'),
                const SizedBox(height: 16),
                ElevatedButton.icon(
                  onPressed: createInvoice,
                  icon: const Icon(Icons.add),
                  label: const Text('Eerste factuur aanmaken'),
                ),
              ],
            ),
          );
        }

        // Success state
        return ListView.builder(
          itemCount: snapshot.data!.length,
          itemBuilder: (context, index) {
            return InvoiceListTile(invoice: snapshot.data![index]);
          },
        );
      },
    );
  }
}
```

### 7. Use const Constructors for Performance

Optimize rebuild performance:

```dart
// Good - const widgets won't rebuild
const Text('Facturen')
const Icon(Icons.add)
const SizedBox(height: 16)
const EdgeInsets.all(16.0)

// Bad - creates new instance every build
Text('Facturen')
Icon(Icons.add)
SizedBox(height: 16)
```

### 8. Implement Proper Form Validation with Dutch Messages

```dart
TextFormField(
  decoration: const InputDecoration(
    labelText: 'BTW-nummer',
  ),
  validator: (value) {
    if (value == null || value.isEmpty) {
      return 'BTW-nummer is verplicht';
    }
    if (!RegExp(r'^NL\d{9}B\d{2}$').hasMatch(value)) {
      return 'Ongeldig BTW-nummer (format: NL000000000B00)';
    }
    return null;
  },
)
```

## Anti-Patterns to Avoid

### 1. Hardcoding Dutch Text Instead of Using Localization

**Bad**:
```dart
Text('Facturen')
Text('Opslaan')
```

**Good**:
```dart
// Use flutter_localizations or easy_localization package
Text(AppLocalizations.of(context)!.invoices)
Text(context.tr('save'))

// Or at minimum, use constants
class AppStrings {
  static const invoices = 'Facturen';
  static const save = 'Opslaan';
}

Text(AppStrings.invoices)
```

### 2. Not Supporting Landscape Orientation

**Bad**:
```dart
// Forcing portrait only
SystemChrome.setPreferredOrientations([
  DeviceOrientation.portraitUp,
]);
```

**Good**:
```dart
// Support all orientations and use OrientationBuilder
OrientationBuilder(
  builder: (context, orientation) {
    if (orientation == Orientation.landscape) {
      return _buildLandscapeLayout();
    }
    return _buildPortraitLayout();
  },
)

// Or use responsive design that works in both orientations
ResponsiveLayout(
  mobile: InvoiceListMobile(),
  tablet: InvoiceListTablet(),
)
```

### 3. Ignoring Platform Conventions

**Bad**:
```dart
// Using Material widgets on iOS without considering platform
MaterialApp(
  home: Scaffold(
    appBar: AppBar(title: Text('App')),
  ),
)
```

**Good**:
```dart
// Provide platform-appropriate experience
Widget build(BuildContext context) {
  if (Platform.isIOS) {
    return CupertinoApp(
      home: CupertinoPageScaffold(
        navigationBar: CupertinoNavigationBar(
          middle: Text('App'),
        ),
        child: SafeArea(child: HomePage()),
      ),
    );
  }

  return MaterialApp(
    home: Scaffold(
      appBar: AppBar(title: Text('App')),
      body: HomePage(),
    ),
  );
}
```

### 4. Not Disposing Controllers

**Bad**:
```dart
class MyForm extends StatefulWidget {
  @override
  State<MyForm> createState() => _MyFormState();
}

class _MyFormState extends State<MyForm> {
  final _controller = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return TextField(controller: _controller);
  }
  // Missing dispose!
}
```

**Good**:
```dart
class _MyFormState extends State<MyForm> {
  final _controller = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return TextField(controller: _controller);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }
}
```

### 5. Using Sized Boxes Instead of Constraints

**Bad**:
```dart
Container(
  width: 300, // Fixed width breaks on small screens
  child: InvoiceCard(),
)
```

**Good**:
```dart
Container(
  constraints: BoxConstraints(
    maxWidth: 600,
    minWidth: 200,
  ),
  child: InvoiceCard(),
)

// Or use responsive design
LayoutBuilder(
  builder: (context, constraints) {
    final width = constraints.maxWidth;
    return Container(
      width: width > 600 ? 600 : width,
      child: InvoiceCard(),
    );
  },
)
```

## Integration with Other Skills

This skill works closely with:

- **testing-expert**: Write widget tests for Flutter components, integration tests for flows
- **ui-ux-expert**: Apply design principles, accessibility standards (WCAG), user experience patterns
- **frontend-debugger**: Debug layout issues, performance problems, platform-specific bugs
- **dutch-tax-compliance**: Implement VAT calculations, invoice requirements, tax reporting
- **security-expert**: Secure local storage, API communication, sensitive data handling
- **api-documentation**: Consume backend APIs, handle authentication, manage data synchronization

## Pre-Release Checklists

### Android Release Checklist

- [ ] App displays correctly in Dutch locale (nl_NL)
- [ ] Currency formatting uses comma as decimal separator (€ 1.250,00)
- [ ] Date formatting uses dd-MM-yyyy format
- [ ] All text is properly localized (no hardcoded strings)
- [ ] Material Design 3 components used consistently
- [ ] Bottom navigation follows Material guidelines
- [ ] FAB placement doesn't conflict with navigation
- [ ] App works in both portrait and landscape
- [ ] Dark mode is properly implemented
- [ ] Proper back button handling
- [ ] App icon meets Android guidelines (adaptive icon)
- [ ] Splash screen configured
- [ ] Minimum SDK version set appropriately (API 21+)
- [ ] ProGuard/R8 rules configured for release
- [ ] Signing configuration set up
- [ ] Version code and name updated
- [ ] Tested on multiple screen sizes (phone, tablet, foldable)
- [ ] Performance profiling completed
- [ ] No memory leaks (controllers disposed)
- [ ] Proper error handling and user feedback

### iOS Release Checklist

- [ ] App displays correctly in Dutch locale (nl_NL)
- [ ] Currency and date formatting correct
- [ ] Cupertino widgets used for iOS-specific UI
- [ ] Navigation bar follows iOS Human Interface Guidelines
- [ ] Tab bar icons and labels are appropriate
- [ ] Action sheets used instead of Material dialogs
- [ ] Safe area insets respected (notch, home indicator)
- [ ] App works on all supported iOS devices
- [ ] Dark mode implemented and tested
- [ ] Proper keyboard handling (dismiss on tap outside)
- [ ] Haptic feedback implemented where appropriate
- [ ] App icon meets iOS requirements (all sizes)
- [ ] Launch screen configured
- [ ] Deployment target set (iOS 12+ recommended)
- [ ] App Transport Security configured
- [ ] Info.plist permissions properly described
- [ ] Code signing set up
- [ ] Version and build number updated
- [ ] Tested on iPhone and iPad
- [ ] Landscape mode works correctly on iPad
- [ ] No warnings in Xcode
- [ ] Accessibility labels for VoiceOver

### Windows Desktop Release Checklist

- [ ] Custom title bar implemented (if needed)
- [ ] Window min/max size constraints set
- [ ] Keyboard shortcuts implemented (Ctrl+N, Ctrl+S, etc.)
- [ ] Context menus (right-click) work properly
- [ ] Mouse hover states on interactive elements
- [ ] Resize behavior works correctly
- [ ] High DPI display support
- [ ] App icon in multiple resolutions
- [ ] Install/uninstall process tested
- [ ] File associations configured (if needed)
- [ ] Auto-update mechanism (if applicable)
- [ ] Startup on login option (if needed)
- [ ] Proper window state persistence
- [ ] Taskbar integration
- [ ] Multi-monitor support tested
- [ ] Performance on low-end hardware verified

### Linux Desktop Release Checklist

- [ ] App displays correctly in Dutch locale (nl_NL)
- [ ] Currency and date formatting correct
- [ ] GTK-style header bar or GNOME integration (optional)
- [ ] Sidebar navigation works correctly
- [ ] Keyboard shortcuts implemented (Ctrl+N, Ctrl+S, etc.)
- [ ] Context menus (right-click) work properly
- [ ] Mouse hover states on interactive elements
- [ ] Window resize behavior works correctly
- [ ] HiDPI/Wayland display support
- [ ] App icon in multiple resolutions (.desktop file)
- [ ] Tested on major distributions (Ubuntu, Fedora, Arch)
- [ ] Snap/Flatpak/AppImage packaging (if distributing)
- [ ] .desktop file properly configured
- [ ] XDG directory standards followed
- [ ] Proper permissions for file access
- [ ] System tray integration (if needed)
- [ ] Native notifications working
- [ ] Multi-monitor support tested
- [ ] Dark mode follows system theme (GTK/GNOME)
- [ ] Performance on various hardware tested
- [ ] Dependencies documented for manual installation

## Resources

- **Flutter Documentation**: https://flutter.dev/docs
- **Material Design 3**: https://m3.material.io/
- **Human Interface Guidelines**: https://developer.apple.com/design/human-interface-guidelines/
- **Pub.dev Packages**: https://pub.dev/
- **Flutter Awesome**: https://flutterawesome.com/
- **Widget Catalog**: https://flutter.dev/docs/development/ui/widgets

## Recommended Packages

```yaml
dependencies:
  flutter:
    sdk: flutter

  # State management
  provider: ^6.0.0

  # HTTP requests
  dio: ^5.0.0

  # Local storage
  shared_preferences: ^2.0.0
  hive: ^2.0.0

  # UI components
  cached_network_image: ^3.0.0
  shimmer: ^3.0.0

  # Platform-specific
  bitsdojo_window: ^0.1.0  # Windows title bar

  # Utilities
  intl: ^0.18.0  # Date/number formatting
  url_launcher: ^6.0.0
```

---

**Remember**: Design for the platform, but share code where possible. Users should feel at home on their device!

---

## Animation Patterns

### 1. Hero Animations for Navigation

**Smooth Transitions Between Screens**:
```dart
// List screen
class InvoiceListTile extends StatelessWidget {
  final Invoice invoice;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => InvoiceDetailScreen(invoice: invoice),
          ),
        );
      },
      child: Card(
        child: Row(
          children: [
            Hero(
              tag: 'invoice-${invoice.id}',
              child: Container(
                width: 60,
                height: 60,
                decoration: BoxDecoration(
                  color: Colors.blue,
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    invoice.invoiceNumber.substring(0, 3),
                    style: TextStyle(color: Colors.white),
                  ),
                ),
              ),
            ),
            SizedBox(width: 16),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(invoice.customerName),
                Text('€ ${invoice.total}'),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

// Detail screen
class InvoiceDetailScreen extends StatelessWidget {
  final Invoice invoice;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Hero(
          tag: 'invoice-${invoice.id}',
          child: Text(invoice.invoiceNumber),
        ),
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            Hero(
              tag: 'invoice-${invoice.id}',
              child: Container(
                width: 120,
                height: 120,
                decoration: BoxDecoration(
                  color: Colors.blue,
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    invoice.invoiceNumber,
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                    ),
                  ),
                ),
              ),
            ),
            // Rest of details
          ],
        ),
      ),
    );
  }
}
```

### 2. Animated List Operations

```dart
class AnimatedInvoiceList extends StatefulWidget {
  @override
  State<AnimatedInvoiceList> createState() => _AnimatedInvoiceListState();
}

class _AnimatedInvoiceListState extends State<AnimatedInvoiceList> {
  final GlobalKey<AnimatedListState> _listKey = GlobalKey();
  final List<Invoice> _invoices = [];

  void _addInvoice(Invoice invoice) {
    final index = _invoices.length;
    _invoices.add(invoice);
    _listKey.currentState?.insertItem(index);
  }

  void _removeInvoice(int index) {
    final removedInvoice = _invoices[index];
    _invoices.removeAt(index);

    _listKey.currentState?.removeItem(
      index,
      (context, animation) => _buildItem(removedInvoice, animation),
    );
  }

  Widget _buildItem(Invoice invoice, Animation<double> animation) {
    return SizeTransition(
      sizeFactor: animation,
      child: FadeTransition(
        opacity: animation,
        child: InvoiceListTile(invoice: invoice),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedList(
      key: _listKey,
      initialItemCount: _invoices.length,
      itemBuilder: (context, index, animation) {
        return _buildItem(_invoices[index], animation);
      },
    );
  }
}
```

---

## Custom Painters for Charts

### Financial Bar Chart

```dart
import 'package:flutter/material.dart';

class FinancialBarChart extends StatelessWidget {
  final List<double> monthlyRevenue;
  final List<String> monthLabels;

  const FinancialBarChart({
    required this.monthlyRevenue,
    required this.monthLabels,
  });

  @override
  Widget build(BuildContext context) {
    return CustomPaint(
      size: Size(double.infinity, 200),
      painter: BarChartPainter(
        data: monthlyRevenue,
        labels: monthLabels,
      ),
    );
  }
}

class BarChartPainter extends CustomPainter {
  final List<double> data;
  final List<String> labels;

  BarChartPainter({required this.data, required this.labels});

  @override
  void paint(Canvas canvas, Size size) {
    final maxValue = data.reduce((a, b) => a > b ? a : b);
    final barWidth = size.width / data.length;
    final barSpacing = barWidth * 0.2;
    final actualBarWidth = barWidth - barSpacing;

    // Draw bars
    for (int i = 0; i < data.length; i++) {
      final barHeight = (data[i] / maxValue) * (size.height - 40);
      final x = i * barWidth + barSpacing / 2;
      final y = size.height - barHeight - 20;

      // Gradient fill
      final gradient = LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: [Colors.blue.shade300, Colors.blue.shade700],
      );

      final paint = Paint()
        ..shader = gradient.createShader(
          Rect.fromLTWH(x, y, actualBarWidth, barHeight),
        );

      // Draw rounded rectangle
      final rect = RRect.fromRectAndRadius(
        Rect.fromLTWH(x, y, actualBarWidth, barHeight),
        Radius.circular(4),
      );
      canvas.drawRRect(rect, paint);

      // Draw value on top
      final textPainter = TextPainter(
        text: TextSpan(
          text: '€${data[i].toStringAsFixed(0)}',
          style: TextStyle(
            color: Colors.black,
            fontSize: 10,
            fontWeight: FontWeight.bold,
          ),
        ),
        textDirection: TextDirection.ltr,
      );
      textPainter.layout();
      textPainter.paint(
        canvas,
        Offset(x + (actualBarWidth - textPainter.width) / 2, y - 15),
      );

      // Draw label
      final labelPainter = TextPainter(
        text: TextSpan(
          text: labels[i],
          style: TextStyle(color: Colors.grey, fontSize: 10),
        ),
        textDirection: TextDirection.ltr,
      );
      labelPainter.layout();
      labelPainter.paint(
        canvas,
        Offset(
          x + (actualBarWidth - labelPainter.width) / 2,
          size.height - 15,
        ),
      );
    }
  }

  @override
  bool shouldRepaint(BarChartPainter oldDelegate) {
    return oldDelegate.data != data || oldDelegate.labels != labels;
  }
}
```

---

## Offline Mode Design

### Sync Manager for Offline Support

```dart
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:hive/hive.dart';

class SyncManager {
  final Box<Invoice> _localInvoices;
  final ApiService _apiService;
  final Connectivity _connectivity = Connectivity();

  SyncManager(this._localInvoices, this._apiService);

  Stream<SyncStatus> syncStream() async* {
    yield SyncStatus.syncing;

    // Check connectivity
    final connectivityResult = await _connectivity.checkConnectivity();
    if (connectivityResult == ConnectivityResult.none) {
      yield SyncStatus.offline;
      return;
    }

    try {
      // Get pending local changes
      final pendingInvoices = _localInvoices.values
          .where((invoice) => invoice.needsSync)
          .toList();

      // Sync to server
      for (final invoice in pendingInvoices) {
        await _apiService.syncInvoice(invoice);
        invoice.needsSync = false;
        await invoice.save();
      }

      // Fetch updates from server
      final serverInvoices = await _apiService.getInvoices();
      for (final invoice in serverInvoices) {
        await _localInvoices.put(invoice.id, invoice);
      }

      yield SyncStatus.synced;
    } catch (e) {
      yield SyncStatus.error;
    }
  }
}

enum SyncStatus { syncing, synced, offline, error }
```

---

## Pull-to-Refresh Pattern

```dart
class InvoiceListScreen extends StatefulWidget {
  @override
  State<InvoiceListScreen> createState() => _InvoiceListScreenState();
}

class _InvoiceListScreenState extends State<InvoiceListScreen> {
  final InvoiceService _service = getIt<InvoiceService>();
  List<Invoice> _invoices = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadInvoices();
  }

  Future<void> _loadInvoices() async {
    setState(() => _isLoading = true);
    try {
      final invoices = await _service.getInvoices();
      setState(() {
        _invoices = invoices;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Fout bij laden facturen')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Facturen')),
      body: RefreshIndicator(
        onRefresh: _loadInvoices,
        child: _isLoading && _invoices.isEmpty
            ? Center(child: CircularProgressIndicator())
            : ListView.builder(
                physics: AlwaysScrollableScrollPhysics(),
                itemCount: _invoices.length,
                itemBuilder: (context, index) {
                  return InvoiceListTile(invoice: _invoices[index]);
                },
              ),
      ),
    );
  }
}
```

---

## Infinite Scroll Pattern

```dart
class InfiniteScrollInvoiceList extends StatefulWidget {
  @override
  State<InfiniteScrollInvoiceList> createState() =>
      _InfiniteScrollInvoiceListState();
}

class _InfiniteScrollInvoiceListState extends State<InfiniteScrollInvoiceList> {
  final ScrollController _scrollController = ScrollController();
  final InvoiceService _service = getIt<InvoiceService>();

  List<Invoice> _invoices = [];
  int _currentPage = 1;
  bool _isLoading = false;
  bool _hasMore = true;

  @override
  void initState() {
    super.initState();
    _loadInvoices();
    _scrollController.addListener(_onScroll);
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent * 0.8) {
      if (!_isLoading && _hasMore) {
        _loadInvoices();
      }
    }
  }

  Future<void> _loadInvoices() async {
    if (_isLoading) return;

    setState(() => _isLoading = true);

    try {
      final newInvoices = await _service.getInvoices(
        page: _currentPage,
        perPage: 20,
      );

      setState(() {
        _invoices.addAll(newInvoices);
        _currentPage++;
        _hasMore = newInvoices.length == 20;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      controller: _scrollController,
      itemCount: _invoices.length + (_hasMore ? 1 : 0),
      itemBuilder: (context, index) {
        if (index == _invoices.length) {
          return Center(
            child: Padding(
              padding: EdgeInsets.all(16),
              child: CircularProgressIndicator(),
            ),
          );
        }
        return InvoiceListTile(invoice: _invoices[index]);
      },
    );
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }
}
```

---

## Multi-Language Support

### Complete Localization Setup

```dart
// Add to pubspec.yaml
dependencies:
  flutter_localizations:
    sdk: flutter
  intl: ^0.18.0

// l10n.yaml
arb-dir: lib/l10n
template-arb-file: app_nl.arb
output-localization-file: app_localizations.dart

// lib/l10n/app_nl.arb
{
  "invoices": "Facturen",
  "expenses": "Uitgaven",
  "dashboard": "Dashboard",
  "totalRevenue": "Totale Omzet",
  "invoiceNumber": "Factuurnummer",
  "customerName": "Klantnaam",
  "dueDate": "Vervaldatum",
  "amount": "Bedrag",
  "status": "Status",
  "paid": "Betaald",
  "unpaid": "Onbetaald",
  "overdue": "Verlopen"
}

// lib/l10n/app_en.arb
{
  "invoices": "Invoices",
  "expenses": "Expenses",
  "dashboard": "Dashboard",
  "totalRevenue": "Total Revenue",
  "invoiceNumber": "Invoice Number",
  "customerName": "Customer Name",
  "dueDate": "Due Date",
  "amount": "Amount",
  "status": "Status",
  "paid": "Paid",
  "unpaid": "Unpaid",
  "overdue": "Overdue"
}

// Generate localization
// flutter gen-l10n

// main.dart
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_gen/gen_l10n/app_localizations.dart';

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      localizationsDelegates: [
        AppLocalizations.delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      supportedLocales: [
        Locale('nl', 'NL'),
        Locale('en', 'US'),
      ],
      locale: Locale('nl', 'NL'),
      home: HomePage(),
    );
  }
}

// Usage in widgets
Text(AppLocalizations.of(context)!.invoices)
```

---

## State Restoration

**Preserve State Across App Restarts**:
```dart
class InvoiceListScreen extends StatefulWidget {
  @override
  State<InvoiceListScreen> createState() => _InvoiceListScreenState();
}

class _InvoiceListScreenState extends State<InvoiceListScreen>
    with RestorationMixin {
  final RestorableInt _selectedTab = RestorableInt(0);
  final RestorableString _searchQuery = RestorableString('');

  @override
  String? get restorationId => 'invoice_list_screen';

  @override
  void restoreState(RestorationBucket? oldBucket, bool initialRestore) {
    registerForRestoration(_selectedTab, 'selected_tab');
    registerForRestoration(_searchQuery, 'search_query');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: TabBarView(
        index: _selectedTab.value,
        children: [
          AllInvoicesTab(searchQuery: _searchQuery.value),
          PaidInvoicesTab(),
          UnpaidInvoicesTab(),
        ],
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedTab.value,
        onDestinationSelected: (index) {
          setState(() => _selectedTab.value = index);
        },
        destinations: [
          NavigationDestination(icon: Icon(Icons.all_inbox), label: 'Alle'),
          NavigationDestination(icon: Icon(Icons.check), label: 'Betaald'),
          NavigationDestination(icon: Icon(Icons.pending), label: 'Onbetaald'),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _selectedTab.dispose();
    _searchQuery.dispose();
    super.dispose();
  }
}
```

---

## Error Boundaries

**Graceful Error Handling**:
```dart
class ErrorBoundary extends StatefulWidget {
  final Widget child;
  final Widget Function(Object error, StackTrace stackTrace)? errorBuilder;

  const ErrorBoundary({
    required this.child,
    this.errorBuilder,
  });

  @override
  State<ErrorBoundary> createState() => _ErrorBoundaryState();
}

class _ErrorBoundaryState extends State<ErrorBoundary> {
  Object? _error;
  StackTrace? _stackTrace;

  @override
  void initState() {
    super.initState();
    FlutterError.onError = (details) {
      setState(() {
        _error = details.exception;
        _stackTrace = details.stack;
      });
    };
  }

  @override
  Widget build(BuildContext context) {
    if (_error != null) {
      if (widget.errorBuilder != null) {
        return widget.errorBuilder!(_error!, _stackTrace!);
      }

      return Scaffold(
        appBar: AppBar(title: Text('Er ging iets mis')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline, size: 64, color: Colors.red),
              SizedBox(height: 16),
              Text(
                'Er is een fout opgetreden',
                style: Theme.of(context).textTheme.headlineSmall,
              ),
              SizedBox(height: 8),
              Text(_error.toString()),
              SizedBox(height: 24),
              ElevatedButton(
                onPressed: () {
                  setState(() {
                    _error = null;
                    _stackTrace = null;
                  });
                },
                child: Text('Opnieuw proberen'),
              ),
            ],
          ),
        ),
      );
    }

    return widget.child;
  }
}

// Usage
void main() {
  runApp(
    ErrorBoundary(
      child: MyApp(),
    ),
  );
}
```

---

## Accessibility Testing

**Test with Screen Reader**:
```dart
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('Invoice tile is accessible', (tester) async {
    final invoice = Invoice(
      id: '1',
      invoiceNumber: 'INV-001',
      customerName: 'Acme B.V.',
      total: 1250.00,
    );

    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: InvoiceListTile(invoice: invoice),
        ),
      ),
    );

    // Check semantic labels
    expect(
      find.bySemanticsLabel('Factuur INV-001 voor Acme B.V., bedrag € 1.250,00'),
      findsOneWidget,
    );

    // Check contrast ratio
    final text = tester.widget<Text>(find.text('INV-001'));
    expect(text.style?.color, isNot(equals(Colors.white)));
  });
}
```

---

## Performance Best Practices

### Image Optimization

```dart
// Use cached images
CachedNetworkImage(
  imageUrl: invoice.logoUrl,
  placeholder: (context, url) => Shimmer.fromColors(
    baseColor: Colors.grey.shade300,
    highlightColor: Colors.grey.shade100,
    child: Container(width: 100, height: 100, color: Colors.white),
  ),
  errorWidget: (context, url, error) => Icon(Icons.error),
  memCacheWidth: 200, // Resize in memory
  maxHeightDiskCache: 200,
)

// Lazy load images
Image.network(
  imageUrl,
  loadingBuilder: (context, child, loadingProgress) {
    if (loadingProgress == null) return child;
    return CircularProgressIndicator(
      value: loadingProgress.expectedTotalBytes != null
          ? loadingProgress.cumulativeBytesLoaded /
              loadingProgress.expectedTotalBytes!
          : null,
    );
  },
)
```

---

**Version 3.0.0** - Enhanced with animations, custom painters, offline mode, pull-to-refresh, infinite scroll, multi-language support, state restoration, error boundaries, accessibility testing, and performance best practices
