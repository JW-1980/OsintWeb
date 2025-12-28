---
name: flutter
description: Flutter and Dart development for Android, iOS, Windows, Linux, Web - widgets, state management, cross-platform apps
version: 1.1.3
tags: [flutter, dart, mobile, desktop, linux, windows, android, ios, widgets, provider, state-management]
trigger_keywords: [sk-flutter, "flutter widget", "flutter screen", "dart class", "provider pattern", "flutter navigation", "stateful widget", "flutter app", "mobile development", "flutter state", "bloc pattern", "flutter ui", "dart code", "flutter build"]
related_skills: [ui-ux-expert, flutter-app-design, laravel-ecosystem]
---
# Flutter & Dart Expert

Use this skill when working with Flutter mobile/web/desktop applications (Android, iOS, Windows, Linux), Dart programming language features, widget development, state management, or cross-platform app development.

## Supported Platforms

| Platform | Status | Notes |
|----------|--------|-------|
| Android | Full Support | Material Design 3, API 21+ |
| iOS | Full Support | Cupertino widgets, iOS 12+ |
| Windows | Full Support | Win32/UWP, Windows 10+ |
| Linux | Full Support | GTK, Ubuntu/Fedora/Arch |
| Web | Full Support | HTML/Canvas renderer |
| macOS | Full Support | Cocoa, macOS 10.14+ |

## Core Dart Language Expertise

### 1. Dart Fundamentals

**Type System:**
```dart
// Strong typing with type inference
String name = 'Flutter'; // Explicit type
var count = 0; // Type inferred as int
final url = 'https://api.example.com'; // Immutable variable
const pi = 3.14159; // Compile-time constant

// Null safety (sound null safety enabled by default)
String? nullableName; // Can be null
String nonNullableName = 'Required'; // Cannot be null
late String lateInitialized; // Initialized later

// Null-aware operators
String? middleName;
String fullName = 'John ${middleName ?? "N/A"} Doe'; // Null coalescing
int? length = nullableName?.length; // Null-aware access
```

**Collections:**
```dart
// Lists
final List<String> fruits = ['Apple', 'Banana', 'Orange'];
final numbers = <int>[1, 2, 3]; // Type annotation shorthand
final spread = [...fruits, 'Mango']; // Spread operator

// Maps
final Map<String, int> scores = {'Alice': 95, 'Bob': 87};
final user = <String, dynamic>{
  'name': 'John',
  'age': 30,
  'email': '[email protected]',
};

// Sets
final Set<int> uniqueNumbers = {1, 2, 3, 4};
```

**Functions:**
```dart
// Named parameters
void greet({required String name, String greeting = 'Hello'}) {
  print('$greeting, $name!');
}
greet(name: 'Alice'); // greeting uses default

// Arrow functions
int double(int x) => x * 2;

// First-class functions
List<int> applyFunction(List<int> list, int Function(int) fn) {
  return list.map(fn).toList();
}

// Async functions
Future<String> fetchData() async {
  await Future.delayed(Duration(seconds: 2));
  return 'Data loaded';
}
```

**Classes & Objects:**
```dart
// Modern class syntax
class User {
  final String id;
  final String name;
  final String? email;

  // Constructor with named parameters
  User({
    required this.id,
    required this.name,
    this.email,
  });

  // Factory constructor
  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as String,
      name: json['name'] as String,
      email: json['email'] as String?,
    );
  }

  // Method
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      if (email != null) 'email': email,
    };
  }

  // Copy with method (immutability pattern)
  User copyWith({String? name, String? email}) {
    return User(
      id: id,
      name: name ?? this.name,
      email: email ?? this.email,
    );
  }
}

// Mixins for composition
mixin Timestamped {
  DateTime createdAt = DateTime.now();
}

class Document with Timestamped {
  String title;
  Document(this.title);
}
```

**Enums:**
```dart
// Enhanced enums (Dart 2.17+)
enum Status {
  pending(color: Colors.orange, label: 'Pending'),
  approved(color: Colors.green, label: 'Approved'),
  rejected(color: Colors.red, label: 'Rejected');

  final Color color;
  final String label;

  const Status({required this.color, required this.label});
}

// Usage
Status current = Status.pending;
print(current.label); // 'Pending'
```

### 2. Asynchronous Programming

**Futures:**
```dart
// Future basics
Future<String> fetchUser(String id) async {
  final response = await http.get(Uri.parse('https://api.example.com/users/$id'));
  if (response.statusCode == 200) {
    return response.body;
  } else {
    throw Exception('Failed to load user');
  }
}

// Error handling
try {
  final user = await fetchUser('123');
  print(user);
} on SocketException {
  print('No internet connection');
} on HttpException {
  print('HTTP error');
} catch (e) {
  print('Unexpected error: $e');
} finally {
  print('Request completed');
}

// Future.wait for parallel execution
final results = await Future.wait([
  fetchUser('1'),
  fetchUser('2'),
  fetchUser('3'),
]);
```

**Streams:**
```dart
// Stream basics
Stream<int> countStream(int max) async* {
  for (int i = 1; i <= max; i++) {
    await Future.delayed(Duration(seconds: 1));
    yield i;
  }
}

// Listening to streams
await for (final count in countStream(5)) {
  print(count); // Prints 1, 2, 3, 4, 5 with 1-second delays
}

// Stream transformations
final stream = Stream.fromIterable([1, 2, 3, 4, 5]);
final doubled = stream.map((n) => n * 2);
final evens = stream.where((n) => n % 2 == 0);

// StreamController for custom streams
final controller = StreamController<String>();
controller.stream.listen((data) => print(data));
controller.add('Hello');
controller.add('World');
controller.close();
```

## Flutter Framework Essentials

### 1. Widget Fundamentals

**Stateless vs Stateful Widgets:**
```dart
// Stateless Widget (immutable UI)
class GreetingCard extends StatelessWidget {
  final String name;

  const GreetingCard({Key? key, required this.name}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Text('Hello, $name!'),
      ),
    );
  }
}

// Stateful Widget (mutable state)
class Counter extends StatefulWidget {
  const Counter({Key? key}) : super(key: key);

  @override
  State<Counter> createState() => _CounterState();
}

class _CounterState extends State<Counter> {
  int _count = 0;

  void _increment() {
    setState(() {
      _count++;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text('Count: $_count'),
        ElevatedButton(
          onPressed: _increment,
          child: const Text('Increment'),
        ),
      ],
    );
  }
}
```

**Layout Widgets:**
```dart
// Column & Row
Column(
  mainAxisAlignment: MainAxisAlignment.center,
  crossAxisAlignment: CrossAxisAlignment.start,
  children: [
    Text('Title'),
    Text('Subtitle'),
  ],
)

// Stack for overlaying widgets
Stack(
  children: [
    Container(width: 200, height: 200, color: Colors.blue),
    Positioned(
      bottom: 10,
      right: 10,
      child: Icon(Icons.star, color: Colors.white),
    ),
  ],
)

// Flexible & Expanded for responsive layouts
Row(
  children: [
    Expanded(flex: 2, child: Container(color: Colors.red)),
    Expanded(flex: 1, child: Container(color: Colors.blue)),
  ],
)
```

**Common Widgets:**
```dart
// Container with decoration
Container(
  padding: EdgeInsets.all(16),
  margin: EdgeInsets.symmetric(horizontal: 8),
  decoration: BoxDecoration(
    color: Colors.white,
    borderRadius: BorderRadius.circular(8),
    boxShadow: [
      BoxShadow(
        color: Colors.black12,
        blurRadius: 4,
        offset: Offset(0, 2),
      ),
    ],
  ),
  child: Text('Styled Container'),
)

// ListView for scrollable lists
ListView.builder(
  itemCount: items.length,
  itemBuilder: (context, index) {
    return ListTile(
      leading: Icon(Icons.person),
      title: Text(items[index].name),
      subtitle: Text(items[index].email),
      onTap: () => handleTap(items[index]),
    );
  },
)

// GridView for grid layouts
GridView.builder(
  gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
    crossAxisCount: 2,
    crossAxisSpacing: 8,
    mainAxisSpacing: 8,
  ),
  itemCount: products.length,
  itemBuilder: (context, index) {
    return ProductCard(product: products[index]);
  },
)
```

### 2. State Management Patterns

**Provider Pattern (Recommended for Most Apps):**
```dart
// Model class
class CartModel extends ChangeNotifier {
  final List<Product> _items = [];

  List<Product> get items => List.unmodifiable(_items);

  int get itemCount => _items.length;

  double get totalPrice => _items.fold(0, (sum, item) => sum + item.price);

  void addProduct(Product product) {
    _items.add(product);
    notifyListeners(); // Notify widgets to rebuild
  }

  void removeProduct(String productId) {
    _items.removeWhere((item) => item.id == productId);
    notifyListeners();
  }

  void clear() {
    _items.clear();
    notifyListeners();
  }
}

// Provide the model
void main() {
  runApp(
    ChangeNotifierProvider(
      create: (_) => CartModel(),
      child: MyApp(),
    ),
  );
}

// Consume the model
class CartSummary extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Consumer<CartModel>(
      builder: (context, cart, child) {
        return Text('Total: \$${cart.totalPrice.toStringAsFixed(2)}');
      },
    );
  }
}

// Access without rebuilding
void addToCart(BuildContext context, Product product) {
  context.read<CartModel>().addProduct(product);
  // Alternative: Provider.of<CartModel>(context, listen: false).addProduct(product);
}
```

**Riverpod (Modern Alternative):**
```dart
// Define providers
final counterProvider = StateProvider<int>((ref) => 0);

final userProvider = FutureProvider<User>((ref) async {
  final response = await http.get(Uri.parse('https://api.example.com/user'));
  return User.fromJson(jsonDecode(response.body));
});

// Consume in widget
class CounterView extends ConsumerWidget {
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final count = ref.watch(counterProvider);

    return Column(
      children: [
        Text('Count: $count'),
        ElevatedButton(
          onPressed: () => ref.read(counterProvider.notifier).state++,
          child: Text('Increment'),
        ),
      ],
    );
  }
}
```

**Bloc Pattern (Complex Apps):**
```dart
// Events
abstract class CounterEvent {}
class IncrementEvent extends CounterEvent {}
class DecrementEvent extends CounterEvent {}

// States
class CounterState {
  final int count;
  const CounterState(this.count);
}

// Bloc
class CounterBloc extends Bloc<CounterEvent, CounterState> {
  CounterBloc() : super(CounterState(0)) {
    on<IncrementEvent>((event, emit) {
      emit(CounterState(state.count + 1));
    });

    on<DecrementEvent>((event, emit) {
      emit(CounterState(state.count - 1));
    });
  }
}

// Usage
BlocBuilder<CounterBloc, CounterState>(
  builder: (context, state) {
    return Text('Count: ${state.count}');
  },
)
```

### 3. Navigation & Routing

**Basic Navigation:**
```dart
// Push a new route
Navigator.push(
  context,
  MaterialPageRoute(builder: (context) => DetailScreen(item: item)),
);

// Pop back
Navigator.pop(context);

// Replace current route
Navigator.pushReplacement(
  context,
  MaterialPageRoute(builder: (context) => HomeScreen()),
);

// Push and clear all previous routes
Navigator.pushAndRemoveUntil(
  context,
  MaterialPageRoute(builder: (context) => LoginScreen()),
  (route) => false,
);
```

**Named Routes:**
```dart
// Define routes
MaterialApp(
  initialRoute: '/',
  routes: {
    '/': (context) => HomeScreen(),
    '/details': (context) => DetailsScreen(),
    '/settings': (context) => SettingsScreen(),
  },
)

// Navigate using named routes
Navigator.pushNamed(context, '/details', arguments: {'id': '123'});

// Extract arguments
class DetailsScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final args = ModalRoute.of(context)!.settings.arguments as Map<String, String>;
    final id = args['id'];
    return Text('Details for: $id');
  }
}
```

**Go Router (Recommended for Complex Apps):**
```dart
final router = GoRouter(
  routes: [
    GoRoute(
      path: '/',
      builder: (context, state) => HomeScreen(),
      routes: [
        GoRoute(
          path: 'details/:id',
          builder: (context, state) {
            final id = state.params['id']!;
            return DetailsScreen(id: id);
          },
        ),
      ],
    ),
  ],
);

// Navigate
context.go('/details/123');
context.push('/settings');
```

### 4. Forms & Validation

**Form Handling:**
```dart
class LoginForm extends StatefulWidget {
  @override
  State<LoginForm> createState() => _LoginFormState();
}

class _LoginFormState extends State<LoginForm> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _submit() {
    if (_formKey.currentState!.validate()) {
      // Form is valid, proceed with login
      final email = _emailController.text;
      final password = _passwordController.text;
      print('Login: $email');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Form(
      key: _formKey,
      child: Column(
        children: [
          TextFormField(
            controller: _emailController,
            decoration: InputDecoration(labelText: 'Email'),
            keyboardType: TextInputType.emailAddress,
            validator: (value) {
              if (value == null || value.isEmpty) {
                return 'Please enter your email';
              }
              if (!value.contains('@')) {
                return 'Please enter a valid email';
              }
              return null;
            },
          ),
          TextFormField(
            controller: _passwordController,
            decoration: InputDecoration(labelText: 'Password'),
            obscureText: true,
            validator: (value) {
              if (value == null || value.isEmpty) {
                return 'Please enter your password';
              }
              if (value.length < 6) {
                return 'Password must be at least 6 characters';
              }
              return null;
            },
          ),
          ElevatedButton(
            onPressed: _submit,
            child: Text('Login'),
          ),
        ],
      ),
    );
  }
}
```

### 5. Network Requests & JSON

**HTTP Requests:**
```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

// GET request
Future<List<Post>> fetchPosts() async {
  final response = await http.get(
    Uri.parse('https://jsonplaceholder.typicode.com/posts'),
  );

  if (response.statusCode == 200) {
    final List<dynamic> json = jsonDecode(response.body);
    return json.map((item) => Post.fromJson(item)).toList();
  } else {
    throw Exception('Failed to load posts');
  }
}

// POST request
Future<Post> createPost(Post post) async {
  final response = await http.post(
    Uri.parse('https://jsonplaceholder.typicode.com/posts'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode(post.toJson()),
  );

  if (response.statusCode == 201) {
    return Post.fromJson(jsonDecode(response.body));
  } else {
    throw Exception('Failed to create post');
  }
}

// Model with JSON serialization
class Post {
  final int id;
  final String title;
  final String body;

  Post({required this.id, required this.title, required this.body});

  factory Post.fromJson(Map<String, dynamic> json) {
    return Post(
      id: json['id'] as int,
      title: json['title'] as String,
      body: json['body'] as String,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'body': body,
    };
  }
}
```

**FutureBuilder Pattern:**
```dart
class PostsList extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<Post>>(
      future: fetchPosts(),
      builder: (context, snapshot) {
        if (snapshot.hasData) {
          return ListView.builder(
            itemCount: snapshot.data!.length,
            itemBuilder: (context, index) {
              final post = snapshot.data![index];
              return ListTile(
                title: Text(post.title),
                subtitle: Text(post.body),
              );
            },
          );
        } else if (snapshot.hasError) {
          return Center(child: Text('Error: ${snapshot.error}'));
        }
        return Center(child: CircularProgressIndicator());
      },
    );
  }
}
```

### 6. Local Storage

**SharedPreferences (Simple Key-Value):**
```dart
import 'package:shared_preferences/shared_preferences.dart';

// Save data
Future<void> saveUser(String username) async {
  final prefs = await SharedPreferences.getInstance();
  await prefs.setString('username', username);
  await prefs.setBool('isLoggedIn', true);
  await prefs.setInt('loginCount', (prefs.getInt('loginCount') ?? 0) + 1);
}

// Retrieve data
Future<String?> getUsername() async {
  final prefs = await SharedPreferences.getInstance();
  return prefs.getString('username');
}
```

**Hive (NoSQL Database):**
```dart
import 'package:hive/hive.dart';

// Define model
@HiveType(typeId: 0)
class User extends HiveObject {
  @HiveField(0)
  final String id;

  @HiveField(1)
  final String name;

  @HiveField(2)
  final String email;

  User({required this.id, required this.name, required this.email});
}

// Initialize and use
await Hive.initFlutter();
Hive.registerAdapter(UserAdapter());
final box = await Hive.openBox<User>('users');

// CRUD operations
await box.put('user1', User(id: '1', name: 'Alice', email: '[email protected]'));
final user = box.get('user1');
await box.delete('user1');
```

### 7. Performance Optimization

**Best Practices:**
```dart
// Use const constructors
const Text('Hello'); // Compiled once, reused

// Avoid rebuilding expensive widgets
class ExpensiveWidget extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return const MyComplexWidget(); // Const prevents rebuilds
  }
}

// Extract static widgets
class MyPage extends StatelessWidget {
  static const _header = Text('Header'); // Doesn't rebuild

  @override
  Widget build(BuildContext context) {
    return Column(children: [_header, DynamicContent()]);
  }
}

// Use ListView.builder instead of ListView for long lists
ListView.builder(
  itemCount: 1000,
  itemBuilder: (context, index) => ListTile(title: Text('Item $index')),
)

// Lazy load images
Image.network(
  'https://example.com/image.jpg',
  loadingBuilder: (context, child, loadingProgress) {
    if (loadingProgress == null) return child;
    return CircularProgressIndicator();
  },
)
```

## Testing Best Practices

**Unit Tests:**
```dart
// test/models/user_test.dart
import 'package:test/test.dart';

void main() {
  group('User', () {
    test('fromJson creates User from map', () {
      final json = {'id': '1', 'name': 'Alice', 'email': '[email protected]'};
      final user = User.fromJson(json);

      expect(user.id, '1');
      expect(user.name, 'Alice');
      expect(user.email, '[email protected]');
    });

    test('toJson converts User to map', () {
      final user = User(id: '1', name: 'Alice', email: '[email protected]');
      final json = user.toJson();

      expect(json['id'], '1');
      expect(json['name'], 'Alice');
    });
  });
}
```

**Widget Tests:**
```dart
// test/widgets/counter_test.dart
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('Counter increments when button is pressed', (tester) async {
    await tester.pumpWidget(MaterialApp(home: Counter()));

    expect(find.text('Count: 0'), findsOneWidget);

    await tester.tap(find.byType(ElevatedButton));
    await tester.pump(); // Trigger rebuild

    expect(find.text('Count: 1'), findsOneWidget);
  });
}
```

## Common Patterns & Best Practices

### Architecture Patterns

**Feature-First Structure:**
```
lib/
├── core/
│   ├── theme/
│   ├── constants/
│   └── utils/
├── features/
│   ├── auth/
│   │   ├── models/
│   │   ├── providers/
│   │   ├── screens/
│   │   └── widgets/
│   └── products/
│       ├── models/
│       ├── providers/
│       ├── screens/
│       └── widgets/
└── main.dart
```

**Clean Architecture:**
```
lib/
├── domain/         # Business logic, entities
├── data/          # Repositories, data sources
├── presentation/  # UI, widgets, state management
└── core/          # Shared utilities
```

### Common Anti-Patterns to Avoid

❌ **Building widgets in methods instead of separate classes**
```dart
// Bad
Widget _buildTitle() {
  return Text('Title');
}

// Good
class TitleWidget extends StatelessWidget {
  @override
  Widget build(BuildContext context) => Text('Title');
}
```

❌ **Not disposing controllers**
```dart
// Bad - memory leak
final controller = TextEditingController();

// Good
@override
void dispose() {
  controller.dispose();
  super.dispose();
}
```

❌ **Using context after async gap**
```dart
// Bad - context might be invalid
Future<void> loadData() async {
  await fetchData();
  Navigator.push(context, ...); // Context might be invalid!
}

// Good
Future<void> loadData() async {
  final data = await fetchData();
  if (mounted) {
    Navigator.push(context, ...);
  }
}
```

## When to Use This Skill

- Building Flutter mobile, web, or desktop applications
- Implementing state management solutions
- Designing widget architecture
- Optimizing app performance
- Setting up navigation and routing
- Handling forms and validation
- Implementing network requests and data persistence
- Writing tests for Flutter apps
- Debugging build errors or performance issues
- Choosing appropriate architecture patterns

## Key Principles

1. **Composition over inheritance** - Build complex UIs from simple widgets
2. **Immutability** - Prefer immutable data structures and `const` constructors
3. **Separation of concerns** - Keep business logic separate from UI
4. **Reactive programming** - Use streams and futures for async operations
5. **Performance-first** - Use const, lazy loading, and efficient rebuilds
6. **Test-driven** - Write tests for critical business logic
7. **Platform awareness** - Respect platform conventions (Material vs Cupertino)
8. **Accessibility** - Use semantic labels and support screen readers

---

## Advanced State Management for Bookkeeping Apps

### GetX Pattern (Lightweight & Powerful)

```dart
import 'package:get/get.dart';

// Controller for invoice management
class InvoiceController extends GetxController {
  final ApiService _apiService = Get.find();

  // Observable state
  final invoices = <Invoice>[].obs;
  final isLoading = false.obs;
  final selectedInvoice = Rx<Invoice?>(null);

  @override
  void onInit() {
    super.onInit();
    fetchInvoices();
  }

  Future<void> fetchInvoices() async {
    try {
      isLoading.value = true;
      final data = await _apiService.getInvoices();
      invoices.value = data;
    } catch (e) {
      Get.snackbar('Error', 'Failed to load invoices: $e');
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> createInvoice(Invoice invoice) async {
    try {
      isLoading.value = true;
      final created = await _apiService.createInvoice(invoice);
      invoices.add(created);
      Get.back(); // Navigate back
      Get.snackbar('Success', 'Invoice created');
    } catch (e) {
      Get.snackbar('Error', 'Failed to create invoice: $e');
    } finally {
      isLoading.value = false;
    }
  }

  void selectInvoice(Invoice invoice) {
    selectedInvoice.value = invoice;
  }

  // Computed values
  double get totalRevenue => invoices.fold(0, (sum, inv) => sum + inv.total);
  int get unpaidCount => invoices.where((inv) => !inv.isPaid).length;
}

// Widget usage
class InvoiceListScreen extends StatelessWidget {
  final InvoiceController controller = Get.put(InvoiceController());

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Invoices')),
      body: Obx(() {
        if (controller.isLoading.value) {
          return Center(child: CircularProgressIndicator());
        }

        return ListView.builder(
          itemCount: controller.invoices.length,
          itemBuilder: (context, index) {
            final invoice = controller.invoices[index];
            return InvoiceTile(invoice: invoice);
          },
        );
      }),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Get.to(() => CreateInvoiceScreen()),
        child: Icon(Icons.add),
      ),
    );
  }
}
```

### MobX Pattern (Reactive Programming)

```dart
import 'package:mobx/mobx.dart';

part 'company_store.g.dart';

class CompanyStore = _CompanyStore with _$CompanyStore;

abstract class _CompanyStore with Store {
  final ApiService apiService;

  _CompanyStore(this.apiService);

  @observable
  Company? currentCompany;

  @observable
  ObservableList<Company> companies = ObservableList<Company>();

  @observable
  bool isLoading = false;

  @computed
  bool get hasCompanies => companies.isNotEmpty;

  @computed
  String get companyName => currentCompany?.name ?? 'No company';

  @action
  Future<void> loadCompanies() async {
    isLoading = true;
    try {
      final data = await apiService.getCompanies();
      companies = ObservableList.of(data);
      if (companies.isNotEmpty && currentCompany == null) {
        currentCompany = companies.first;
      }
    } finally {
      isLoading = false;
    }
  }

  @action
  void switchCompany(Company company) {
    currentCompany = company;
    // Notify other stores
  }
}

// Widget usage with Observer
class CompanySwitcher extends StatelessWidget {
  final CompanyStore store = getIt<CompanyStore>();

  @override
  Widget build(BuildContext context) {
    return Observer(
      builder: (_) {
        if (store.isLoading) {
          return CircularProgressIndicator();
        }

        return DropdownButton<Company>(
          value: store.currentCompany,
          items: store.companies.map((company) {
            return DropdownMenuItem(
              value: company,
              child: Text(company.name),
            );
          }).toList(),
          onChanged: (company) {
            if (company != null) {
              store.switchCompany(company);
            }
          },
        );
      },
    );
  }
}
```

### Redux Pattern (Predictable State)

```dart
// State
class AppState {
  final List<Invoice> invoices;
  final bool isLoading;
  final String? error;

  AppState({
    required this.invoices,
    required this.isLoading,
    this.error,
  });

  factory AppState.initial() => AppState(
    invoices: [],
    isLoading: false,
    error: null,
  );

  AppState copyWith({
    List<Invoice>? invoices,
    bool? isLoading,
    String? error,
  }) {
    return AppState(
      invoices: invoices ?? this.invoices,
      isLoading: isLoading ?? this.isLoading,
      error: error ?? this.error,
    );
  }
}

// Actions
abstract class InvoiceAction {}

class LoadInvoicesAction extends InvoiceAction {}
class InvoicesLoadedAction extends InvoiceAction {
  final List<Invoice> invoices;
  InvoicesLoadedAction(this.invoices);
}
class InvoiceErrorAction extends InvoiceAction {
  final String error;
  InvoiceErrorAction(this.error);
}

// Reducer
AppState invoiceReducer(AppState state, dynamic action) {
  if (action is LoadInvoicesAction) {
    return state.copyWith(isLoading: true, error: null);
  } else if (action is InvoicesLoadedAction) {
    return state.copyWith(
      invoices: action.invoices,
      isLoading: false,
    );
  } else if (action is InvoiceErrorAction) {
    return state.copyWith(
      isLoading: false,
      error: action.error,
    );
  }
  return state;
}

// Middleware for async operations
void invoiceMiddleware(
  Store<AppState> store,
  dynamic action,
  NextDispatcher next,
) {
  if (action is LoadInvoicesAction) {
    ApiService().getInvoices().then((invoices) {
      store.dispatch(InvoicesLoadedAction(invoices));
    }).catchError((error) {
      store.dispatch(InvoiceErrorAction(error.toString()));
    });
  }

  next(action);
}
```

---

## Troubleshooting Common Flutter Issues

### Problem 1: "setState() called after dispose()"

**Symptoms**: Exception when navigating away from screen during async operation

**Solution**:
```dart
class MyWidget extends StatefulWidget {
  @override
  State<MyWidget> createState() => _MyWidgetState();
}

class _MyWidgetState extends State<MyWidget> {
  bool _isLoading = false;

  Future<void> loadData() async {
    setState(() => _isLoading = true);

    final data = await fetchData();

    // ✅ GOOD: Check if widget is still mounted
    if (mounted) {
      setState(() {
        _isLoading = false;
        // Update other state
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return _isLoading ? CircularProgressIndicator() : DataDisplay();
  }
}
```

### Problem 2: Context Used After Navigator.pop()

**Symptoms**: "Looking up a deactivated widget's ancestor is unsafe"

**Solution**:
```dart
// ❌ BAD: Context becomes invalid after pop
void saveAndClose() async {
  await saveData();
  Navigator.pop(context);
  ScaffoldMessenger.of(context).showSnackBar(...); // ERROR!
}

// ✅ GOOD: Get ScaffoldMessenger before pop
void saveAndClose() async {
  final messenger = ScaffoldMessenger.of(context);
  await saveData();
  Navigator.pop(context);
  messenger.showSnackBar(
    SnackBar(content: Text('Saved successfully')),
  );
}
```

### Problem 3: Memory Leaks from Controllers

**Symptoms**: App memory usage grows over time

**Solution**:
```dart
class _MyScreenState extends State<MyScreen> {
  late TextEditingController _nameController;
  late ScrollController _scrollController;
  StreamSubscription? _subscription;

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController();
    _scrollController = ScrollController();

    // Subscribe to streams
    _subscription = someStream.listen((data) {
      // Handle data
    });
  }

  @override
  void dispose() {
    // ✅ ALWAYS dispose controllers and subscriptions
    _nameController.dispose();
    _scrollController.dispose();
    _subscription?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Container();
}
```

### Problem 4: Slow List Rendering

**Symptoms**: UI freezes when displaying large lists

**Solution**:
```dart
// ❌ BAD: Renders all items at once
ListView(
  children: items.map((item) => ItemWidget(item)).toList(),
)

// ✅ GOOD: Lazy loading with builder
ListView.builder(
  itemCount: items.length,
  itemBuilder: (context, index) {
    return ItemWidget(items[index]);
  },
)

// ✅ EVEN BETTER: For complex items, use const and keys
ListView.builder(
  itemCount: items.length,
  itemBuilder: (context, index) {
    final item = items[index];
    return ItemWidget(
      key: ValueKey(item.id),
      item: item,
    );
  },
)
```

### Problem 5: Hot Reload Not Working

**Symptoms**: Changes don't appear after hot reload

**Common Causes & Solutions**:
```dart
// Cause 1: Changed main() - requires hot restart
void main() => runApp(MyApp()); // Change here needs restart

// Cause 2: Changed const values - requires hot restart
class MyApp extends StatelessWidget {
  static const String title = 'App'; // Change here needs restart

  @override
  Widget build(BuildContext context) => MaterialApp();
}

// Cause 3: State not preserved correctly
class _MyWidgetState extends State<MyWidget> {
  final int value = 0; // ❌ BAD: Won't update on hot reload

  @override
  void initState() {
    super.initState();
    value = computeValue(); // ✅ GOOD: Recomputes on hot reload
  }
}
```

---

## Security Best Practices for Bookkeeping Apps

### 1. Secure Storage

```dart
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecureStorageService {
  final _storage = FlutterSecureStorage();

  // Store sensitive data encrypted
  Future<void> saveAuthToken(String token) async {
    await _storage.write(key: 'auth_token', value: token);
  }

  Future<String?> getAuthToken() async {
    return await _storage.read(key: 'auth_token');
  }

  Future<void> clearAll() async {
    await _storage.deleteAll();
  }

  // NEVER store these in SharedPreferences:
  // - Authentication tokens
  // - API keys
  // - User passwords
  // - BSN/tax IDs
  // - Bank account numbers
}
```

### 2. Certificate Pinning

```dart
import 'package:dio/dio.dart';
import 'package:dio/adapter.dart';

class ApiClient {
  late Dio dio;

  ApiClient() {
    dio = Dio(BaseOptions(
      baseUrl: 'https://api.bookkeeping.nl',
      connectTimeout: 5000,
      receiveTimeout: 3000,
    ));

    // Certificate pinning
    (dio.httpClientAdapter as DefaultHttpClientAdapter).onHttpClientCreate = (client) {
      client.badCertificateCallback = (cert, host, port) {
        // Verify certificate fingerprint
        final expectedFingerprint = 'AA:BB:CC:DD...'; // Your cert fingerprint
        final certFingerprint = cert.sha256.toString();

        return certFingerprint == expectedFingerprint && host == 'api.bookkeeping.nl';
      };
      return client;
    };
  }
}
```

### 3. Screen Capture Prevention

```dart
import 'package:flutter_windowmanager/flutter_windowmanager.dart';

class SecurityService {
  // Prevent screenshots on sensitive screens
  Future<void> disableScreenCapture() async {
    await FlutterWindowManager.addFlags(FlutterWindowManager.FLAG_SECURE);
  }

  Future<void> enableScreenCapture() async {
    await FlutterWindowManager.clearFlags(FlutterWindowManager.FLAG_SECURE);
  }
}

// Usage in widget
class SensitiveDataScreen extends StatefulWidget {
  @override
  State<SensitiveDataScreen> createState() => _SensitiveDataScreenState();
}

class _SensitiveDataScreenState extends State<SensitiveDataScreen> {
  final _security = SecurityService();

  @override
  void initState() {
    super.initState();
    _security.disableScreenCapture();
  }

  @override
  void dispose() {
    _security.enableScreenCapture();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Tax Information')),
      body: SensitiveTaxData(),
    );
  }
}
```

### 4. Biometric Authentication

```dart
import 'package:local_auth/local_auth.dart';

class BiometricService {
  final LocalAuthentication _auth = LocalAuthentication();

  Future<bool> canUseBiometrics() async {
    try {
      return await _auth.canCheckBiometrics && await _auth.isDeviceSupported();
    } catch (e) {
      return false;
    }
  }

  Future<bool> authenticate() async {
    try {
      return await _auth.authenticate(
        localizedReason: 'Authenticate to access financial data',
        options: const AuthenticationOptions(
          stickyAuth: true,
          biometricOnly: true,
        ),
      );
    } catch (e) {
      return false;
    }
  }
}

// Usage
class SecureLoginScreen extends StatelessWidget {
  final BiometricService _biometric = BiometricService();

  Future<void> handleLogin() async {
    final canUseBiometric = await _biometric.canUseBiometrics();

    if (canUseBiometric) {
      final authenticated = await _biometric.authenticate();

      if (authenticated) {
        // Proceed with login
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => HomeScreen()),
        );
      }
    } else {
      // Fall back to password
      showPasswordDialog();
    }
  }
}
```

---

## Performance Tips for Bookkeeping Apps

### 1. Optimize List Rendering

```dart
// ❌ BAD: Rebuilds entire list on every change
class InvoiceList extends StatelessWidget {
  final List<Invoice> invoices;

  @override
  Widget build(BuildContext context) {
    return ListView(
      children: invoices.map((invoice) {
        return ExpensiveInvoiceWidget(invoice); // Rebuilds all!
      }).toList(),
    );
  }
}

// ✅ GOOD: Use const constructors and builder
class InvoiceList extends StatelessWidget {
  final List<Invoice> invoices;

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      itemCount: invoices.length,
      itemBuilder: (context, index) {
        return InvoiceTile(
          key: ValueKey(invoices[index].id),
          invoice: invoices[index],
        );
      },
    );
  }
}

class InvoiceTile extends StatelessWidget {
  final Invoice invoice;

  const InvoiceTile({Key? key, required this.invoice}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ListTile(
      title: Text(invoice.number),
      subtitle: Text(invoice.customer),
      trailing: Text('\$${invoice.total}'),
    );
  }
}
```

### 2. Image Optimization

```dart
// ❌ BAD: Loads full-size images
Image.network('https://api.example.com/logo.png')

// ✅ GOOD: Use caching and placeholders
CachedNetworkImage(
  imageUrl: 'https://api.example.com/logo.png',
  placeholder: (context, url) => CircularProgressIndicator(),
  errorWidget: (context, url, error) => Icon(Icons.error),
  memCacheWidth: 200, // Resize in memory
  maxHeightDiskCache: 200,
)
```

### 3. Debounce Search Input

```dart
import 'dart:async';

class SearchController {
  Timer? _debounce;

  void onSearchChanged(String query, Function(String) callback) {
    // Cancel previous timer
    _debounce?.cancel();

    // Create new timer
    _debounce = Timer(const Duration(milliseconds: 500), () {
      callback(query);
    });
  }

  void dispose() {
    _debounce?.cancel();
  }
}

// Usage
class SearchWidget extends StatefulWidget {
  @override
  State<SearchWidget> createState() => _SearchWidgetState();
}

class _SearchWidgetState extends State<SearchWidget> {
  final _searchController = SearchController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return TextField(
      onChanged: (query) {
        _searchController.onSearchChanged(query, (q) {
          // Only calls API after 500ms of no typing
          searchInvoices(q);
        });
      },
    );
  }
}
```

---

## Anti-Patterns to Avoid

### ❌ Anti-Pattern 1: Business Logic in Widgets

```dart
// ❌ BAD: Logic mixed with UI
class InvoiceScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: () async {
        // Business logic in widget!
        final response = await http.post(
          Uri.parse('https://api.example.com/invoices'),
          body: jsonEncode({'total': 100}),
        );

        if (response.statusCode == 200) {
          // More logic...
        }
      },
      child: Text('Create Invoice'),
    );
  }
}

// ✅ GOOD: Separate business logic
class InvoiceService {
  Future<Invoice> createInvoice(InvoiceData data) async {
    final response = await http.post(
      Uri.parse('https://api.example.com/invoices'),
      body: jsonEncode(data.toJson()),
    );

    if (response.statusCode == 200) {
      return Invoice.fromJson(jsonDecode(response.body));
    } else {
      throw InvoiceCreationException();
    }
  }
}

class InvoiceScreen extends StatelessWidget {
  final InvoiceService _service = InvoiceService();

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: () => _createInvoice(),
      child: Text('Create Invoice'),
    );
  }

  Future<void> _createInvoice() async {
    try {
      final invoice = await _service.createInvoice(InvoiceData(...));
      // Update UI
    } catch (e) {
      // Handle error
    }
  }
}
```

### ❌ Anti-Pattern 2: Not Using Keys for Dynamic Lists

```dart
// ❌ BAD: No keys for stateful widgets in list
List<StatefulWidget> _buildItems() {
  return items.map((item) => ItemWidget(item)).toList();
}

// ✅ GOOD: Use unique keys
List<Widget> _buildItems() {
  return items.map((item) {
    return ItemWidget(
      key: ValueKey(item.id),
      item: item,
    );
  }).toList();
}
```

### ❌ Anti-Pattern 3: Synchronous Operations in build()

```dart
// ❌ BAD: Heavy computation in build
class ExpensiveWidget extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final result = performExpensiveCalculation(); // SLOW!
    return Text(result);
  }
}

// ✅ GOOD: Compute once, cache result
class ExpensiveWidget extends StatefulWidget {
  @override
  State<ExpensiveWidget> createState() => _ExpensiveWidgetState();
}

class _ExpensiveWidgetState extends State<ExpensiveWidget> {
  late String _result;

  @override
  void initState() {
    super.initState();
    _result = performExpensiveCalculation();
  }

  @override
  Widget build(BuildContext context) {
    return Text(_result);
  }
}
```

### ❌ Anti-Pattern 4: Creating Objects in build()

```dart
// ❌ BAD: Creates new controller on every rebuild
class MyWidget extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final controller = TextEditingController(); // Memory leak!
    return TextField(controller: controller);
  }
}

// ✅ GOOD: Create controller once in stateful widget
class MyWidget extends StatefulWidget {
  @override
  State<MyWidget> createState() => _MyWidgetState();
}

class _MyWidgetState extends State<MyWidget> {
  late TextEditingController _controller;

  @override
  void initState() {
    super.initState();
    _controller = TextEditingController();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return TextField(controller: _controller);
  }
}
```

---

*Version 2.0.0 - Enhanced with advanced state management patterns (GetX, MobX, Redux), comprehensive troubleshooting, security best practices for financial apps, performance optimization, and anti-patterns to avoid*

---

## PART 3: 100+ Flutter & Dart Tips and Best Practices

### Version 3.0.0 (2025-12-18)
- Added 100+ practical tips across 24 categories
- Covers latest Flutter 3.x and Dart 3.x best practices
- Includes 2025 recommendations and modern patterns

---

## Category 1: State Management Tips (10 tips)

**Tip 1: Use Provider for Simple State**
```dart
// Provider is perfect for most apps - simple and Flutter-team recommended
ChangeNotifierProvider(
  create: (_) => CartModel(),
  child: MyApp(),
)
```

**Tip 2: Riverpod for Compile-Time Safety**
```dart
// Riverpod 2.0+ catches provider errors at compile time
final userProvider = FutureProvider<User>((ref) async {
  return await fetchUser();
});

// AsyncValue provides loading/error/data states automatically
ref.watch(userProvider).when(
  data: (user) => Text(user.name),
  loading: () => CircularProgressIndicator(),
  error: (e, st) => Text('Error: $e'),
);
```

**Tip 3: BLoC Event Transformers**
```dart
// Debounce search events to reduce API calls
on<SearchEvent>(
  _onSearch,
  transformer: debounce(const Duration(milliseconds: 300)),
);
```

**Tip 4: Use context.read() for Actions**
```dart
// Use read() for one-time reads (actions), watch() for subscriptions
ElevatedButton(
  onPressed: () => context.read<CartModel>().clear(), // read() for actions
  child: Text('Clear Cart'),
)
```

**Tip 5: Selector for Granular Rebuilds**
```dart
// Only rebuilds when specific value changes
Selector<CartModel, int>(
  selector: (_, cart) => cart.itemCount,
  builder: (_, count, __) => Text('Items: $count'),
)
```

**Tip 6: StateNotifier over ChangeNotifier**
```dart
// StateNotifier provides immutable state updates
class CounterNotifier extends StateNotifier<int> {
  CounterNotifier() : super(0);
  void increment() => state = state + 1;
}
```

**Tip 7: Avoid Overusing Global State**
- Keep state as local as possible
- Only lift state when multiple widgets need it
- Consider ephemeral state (TextField) vs app state (user auth)

**Tip 8: Use ref.invalidate() for Refresh**
```dart
// Force provider to recompute
ref.invalidate(userProvider);
```

**Tip 9: Combine Providers for Derived State**
```dart
final filteredTodosProvider = Provider<List<Todo>>((ref) {
  final filter = ref.watch(filterProvider);
  final todos = ref.watch(todosProvider);
  return todos.where((t) => t.status == filter).toList();
});
```

**Tip 10: BlocObserver for Debugging**
```dart
class AppBlocObserver extends BlocObserver {
  @override
  void onChange(BlocBase bloc, Change change) {
    debugPrint('${bloc.runtimeType} $change');
  }
}
Bloc.observer = AppBlocObserver();
```

---

## Category 2: Widget Optimization Tips (10 tips)

**Tip 11: Always Use const Constructors**
```dart
// const widgets are compiled once and reused
const Text('Hello');
const SizedBox(height: 16);
const EdgeInsets.all(8);
```

**Tip 12: Extract Static Widgets**
```dart
class MyScreen extends StatelessWidget {
  static const _header = Text('Header'); // Never rebuilds

  @override
  Widget build(BuildContext context) {
    return Column(children: [_header, DynamicContent()]);
  }
}
```

**Tip 13: Use ListView.builder for Long Lists**
```dart
// Builds items lazily, only renders visible items
ListView.builder(
  itemCount: 10000,
  itemBuilder: (context, index) => ListTile(title: Text('Item $index')),
)
```

**Tip 14: RepaintBoundary for Expensive Widgets**
```dart
// Isolates repaints to specific subtrees
RepaintBoundary(
  child: ExpensiveAnimatedWidget(),
)
```

**Tip 15: Use ValueKey for Stateful Lists**
```dart
// Helps Flutter track widget identity during reorders
ListView.builder(
  itemBuilder: (context, index) {
    return MyWidget(key: ValueKey(items[index].id));
  },
)
```

**Tip 16: Prefer SizedBox over Container**
```dart
// SizedBox is lighter than Container
const SizedBox(height: 16); // ✅ Efficient
Container(height: 16); // ❌ Heavier
```

**Tip 17: AnimatedBuilder for Animation Optimization**
```dart
// Only rebuilds animated portion, not entire subtree
AnimatedBuilder(
  animation: _controller,
  builder: (context, child) {
    return Transform.rotate(
      angle: _controller.value * 2 * pi,
      child: child, // child is NOT rebuilt
    );
  },
  child: const ExpensiveWidget(), // Built once
)
```

**Tip 18: Use Visibility vs Offstage**
```dart
// Visibility with maintainState: true keeps state
Visibility(visible: false, maintainState: true, child: MyWidget());
// Offstage keeps widget alive but hidden
Offstage(offstage: true, child: MyWidget());
```

**Tip 19: Avoid Deep Nesting**
```dart
// ❌ Bad: Deep nesting hard to read
Container(child: Padding(child: Container(child: ...)))

// ✅ Good: Extract widgets or use decoration
MyCard(child: Text('Content'))
```

**Tip 20: Use AspectRatio for Responsive Sizing**
```dart
// Maintains aspect ratio regardless of parent size
AspectRatio(
  aspectRatio: 16 / 9,
  child: VideoPlayer(),
)
```

---

## Category 3: Testing Tips (8 tips)

**Tip 21: Test Widget Interactions**
```dart
testWidgets('Counter increments', (tester) async {
  await tester.pumpWidget(MaterialApp(home: Counter()));
  await tester.tap(find.byIcon(Icons.add));
  await tester.pump(); // Rebuild after state change
  expect(find.text('1'), findsOneWidget);
});
```

**Tip 22: Use pump() vs pumpAndSettle()**
```dart
await tester.pump(); // Single frame
await tester.pumpAndSettle(); // Wait for all animations
```

**Tip 23: Mock Dependencies with Mocktail**
```dart
class MockUserRepository extends Mock implements UserRepository {}

test('fetches user', () async {
  final mock = MockUserRepository();
  when(() => mock.getUser('1')).thenAnswer((_) async => User(id: '1'));
});
```

**Tip 24: Golden Tests for Visual Regression**
```dart
testWidgets('MyWidget golden', (tester) async {
  await tester.pumpWidget(MyWidget());
  await expectLater(
    find.byType(MyWidget),
    matchesGoldenFile('goldens/my_widget.png'),
  );
});
```

**Tip 25: Integration Tests with patrol**
```dart
patrolTest('user can login', ($) async {
  await $.pumpWidgetAndSettle(MyApp());
  await $(#emailField).enterText('[email protected]');
  await $(#loginButton).tap();
  expect($(#homeScreen), findsOneWidget);
});
```

**Tip 26: Test Edge Cases**
- Empty states, loading states, error states
- Network failures, timeouts
- Invalid inputs, boundary conditions

**Tip 27: Use setUpAll for Expensive Initialization**
```dart
setUpAll(() async {
  // Run once before all tests
  await initializeHive();
});
```

**Tip 28: Test Accessibility**
```dart
testWidgets('has semantic labels', (tester) async {
  await tester.pumpWidget(MyWidget());
  final semantics = tester.getSemantics(find.byType(MyButton));
  expect(semantics.label, 'Submit form');
});
```

---

## Category 4: Animation Tips (8 tips)

**Tip 29: Implicit Animations for Simple Cases**
```dart
// AnimatedContainer handles animation automatically
AnimatedContainer(
  duration: Duration(milliseconds: 300),
  color: isSelected ? Colors.blue : Colors.grey,
  width: isExpanded ? 200 : 100,
)
```

**Tip 30: Hero Animations for Shared Element Transitions**
```dart
// Source screen
Hero(tag: 'product-${product.id}', child: ProductImage(product))

// Destination screen
Hero(tag: 'product-${product.id}', child: ProductDetailImage(product))
```

**Tip 31: Use Curves for Natural Motion**
```dart
AnimatedContainer(
  duration: Duration(milliseconds: 300),
  curve: Curves.easeOutCubic, // Natural deceleration
)
```

**Tip 32: Staggered Animations with Interval**
```dart
final animation1 = CurvedAnimation(
  parent: controller,
  curve: Interval(0.0, 0.5, curve: Curves.easeOut),
);
final animation2 = CurvedAnimation(
  parent: controller,
  curve: Interval(0.5, 1.0, curve: Curves.easeOut),
);
```

**Tip 33: TweenAnimationBuilder for One-Off Animations**
```dart
// No AnimationController needed
TweenAnimationBuilder<double>(
  tween: Tween(begin: 0, end: 1),
  duration: Duration(seconds: 1),
  builder: (context, value, child) {
    return Opacity(opacity: value, child: child);
  },
  child: MyWidget(),
)
```

**Tip 34: AnimatedList for List Changes**
```dart
// Animate insertions and removals
AnimatedList(
  initialItemCount: items.length,
  itemBuilder: (context, index, animation) {
    return SlideTransition(position: animation.drive(...), child: ...);
  },
)
```

**Tip 35: Dispose AnimationControllers**
```dart
late final AnimationController _controller;

@override
void initState() {
  super.initState();
  _controller = AnimationController(vsync: this, duration: Duration(seconds: 1));
}

@override
void dispose() {
  _controller.dispose(); // Always dispose!
  super.dispose();
}
```

**Tip 36: Use vsync with TickerProviderStateMixin**
```dart
class _MyWidgetState extends State<MyWidget> with TickerProviderStateMixin {
  // vsync prevents off-screen animations from consuming resources
  late final _controller = AnimationController(vsync: this);
}
```

---

## Category 5: Navigation Tips (8 tips)

**Tip 37: GoRouter for Declarative Navigation**
```dart
final router = GoRouter(
  routes: [
    GoRoute(
      path: '/invoice/:id',
      builder: (context, state) => InvoiceScreen(id: state.params['id']!),
    ),
  ],
);
```

**Tip 38: Use context.go() vs context.push()**
```dart
context.go('/home'); // Replaces entire stack
context.push('/details'); // Adds to stack (back button works)
```

**Tip 39: Redirect for Auth Guards**
```dart
GoRouter(
  redirect: (context, state) {
    final isLoggedIn = authProvider.isLoggedIn;
    final isLoggingIn = state.subloc == '/login';

    if (!isLoggedIn && !isLoggingIn) return '/login';
    if (isLoggedIn && isLoggingIn) return '/';
    return null;
  },
)
```

**Tip 40: ShellRoute for Persistent UI**
```dart
ShellRoute(
  builder: (context, state, child) {
    return Scaffold(
      body: child,
      bottomNavigationBar: BottomNav(), // Persists across routes
    );
  },
  routes: [...],
)
```

**Tip 41: Extra Parameter for Complex Data**
```dart
context.go('/invoice', extra: InvoiceData(id: '123', items: items));

// Access in destination
final data = GoRouterState.of(context).extra as InvoiceData;
```

**Tip 42: Named Routes for Type Safety**
```dart
class AppRoutes {
  static const home = '/';
  static const invoiceDetail = '/invoice/:id';

  static String invoiceDetailPath(String id) => '/invoice/$id';
}
```

**Tip 43: Use WillPopScope for Back Button**
```dart
WillPopScope(
  onWillPop: () async {
    final shouldPop = await showExitConfirmDialog();
    return shouldPop;
  },
  child: Scaffold(...),
)
```

**Tip 44: Deep Linking Configuration**
```dart
// iOS: Info.plist
// Android: AndroidManifest.xml with intent-filter
GoRouter(
  routes: [...],
  // Handles app://myapp.com/invoice/123
)
```

---

## Category 6: Form & Validation Tips (6 tips)

**Tip 45: Use GlobalKey<FormState>**
```dart
final _formKey = GlobalKey<FormState>();

Form(
  key: _formKey,
  child: Column(children: [...]),
)

void _submit() {
  if (_formKey.currentState!.validate()) {
    _formKey.currentState!.save();
  }
}
```

**Tip 46: AutovalidateMode for UX**
```dart
TextFormField(
  autovalidateMode: AutovalidateMode.onUserInteraction, // Only after first interaction
)
```

**Tip 47: Custom Validators**
```dart
String? validateEmail(String? value) {
  if (value == null || value.isEmpty) return 'Email is required';
  if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value)) {
    return 'Enter a valid email';
  }
  return null;
}
```

**Tip 48: Form Field Formatters**
```dart
TextFormField(
  inputFormatters: [
    FilteringTextInputFormatter.digitsOnly,
    LengthLimitingTextInputFormatter(10),
    CurrencyInputFormatter(),
  ],
)
```

**Tip 49: Focus Management**
```dart
final _emailFocus = FocusNode();
final _passwordFocus = FocusNode();

TextFormField(
  focusNode: _emailFocus,
  onFieldSubmitted: (_) => _passwordFocus.requestFocus(),
)
```

**Tip 50: Dispose TextEditingControllers**
```dart
final _controller = TextEditingController();

@override
void dispose() {
  _controller.dispose();
  super.dispose();
}
```

---

## Category 7: Async Programming Tips (8 tips)

**Tip 51: FutureBuilder with ConnectionState**
```dart
FutureBuilder<User>(
  future: fetchUser(),
  builder: (context, snapshot) {
    switch (snapshot.connectionState) {
      case ConnectionState.waiting:
        return CircularProgressIndicator();
      case ConnectionState.done:
        if (snapshot.hasError) return ErrorWidget(snapshot.error!);
        return UserWidget(snapshot.data!);
      default:
        return SizedBox();
    }
  },
)
```

**Tip 52: StreamBuilder for Real-Time Data**
```dart
StreamBuilder<List<Message>>(
  stream: chatStream,
  builder: (context, snapshot) {
    if (snapshot.hasData) {
      return MessageList(messages: snapshot.data!);
    }
    return LoadingIndicator();
  },
)
```

**Tip 53: Future.wait for Parallel Requests**
```dart
final results = await Future.wait([
  fetchUser(),
  fetchProducts(),
  fetchOrders(),
]);
// All complete in parallel, not sequentially
```

**Tip 54: Completer for Manual Future Control**
```dart
Completer<String> completer = Completer();
// Later...
completer.complete('result');
// Or...
completer.completeError(Exception('error'));
```

**Tip 55: CancelToken with Dio**
```dart
final cancelToken = CancelToken();

dio.get('/data', cancelToken: cancelToken);

// Later, cancel the request
cancelToken.cancel('User cancelled');
```

**Tip 56: Timeout for Network Requests**
```dart
try {
  final result = await fetchData().timeout(Duration(seconds: 10));
} on TimeoutException {
  // Handle timeout
}
```

**Tip 57: AsyncValue with Riverpod**
```dart
// AsyncValue handles loading/error/data automatically
final userAsync = ref.watch(userProvider);

return userAsync.when(
  data: (user) => Text(user.name),
  loading: () => CircularProgressIndicator(),
  error: (e, st) => Text('Error: $e'),
);
```

**Tip 58: Debounce with Timer**
```dart
Timer? _debounce;

void onSearchChanged(String query) {
  _debounce?.cancel();
  _debounce = Timer(Duration(milliseconds: 500), () {
    search(query);
  });
}
```

---

## Category 8: Local Storage Tips (10 tips)

**Tip 59: SharedPreferences for Simple Data**
```dart
final prefs = await SharedPreferences.getInstance();
await prefs.setString('username', 'john');
final username = prefs.getString('username');
```

**Tip 60: Hive for Fast NoSQL Storage**
```dart
await Hive.initFlutter();
Hive.registerAdapter(UserAdapter());
final box = await Hive.openBox<User>('users');
await box.put('user1', user);
```

**Tip 61: Use Hive CE (Community Edition)**
```dart
// Original Hive is abandoned, use hive_ce
// pubspec.yaml: hive_ce: ^latest
```

**Tip 62: Drift for SQL with Type Safety**
```dart
// Drift (previously Moor) provides type-safe SQL
@DriftDatabase(tables: [Invoices, Customers])
class AppDatabase extends _$AppDatabase {
  // Type-safe queries
  Future<List<Invoice>> getUnpaidInvoices() {
    return (select(invoices)..where((t) => t.isPaid.equals(false))).get();
  }
}
```

**Tip 63: Encrypt Sensitive Data in Hive**
```dart
final encryptionKey = await getEncryptionKey();
final encryptedBox = await Hive.openBox('secrets',
  encryptionCipher: HiveAesCipher(encryptionKey),
);
```

**Tip 64: Use Lazy Boxes for Large Data**
```dart
// LazyBox doesn't load entire box into memory
final lazyBox = await Hive.openLazyBox<Invoice>('invoices');
final invoice = await lazyBox.get('inv-001');
```

**Tip 65: Batch Writes for Performance**
```dart
// Batch multiple writes
await box.putAll({'key1': value1, 'key2': value2, 'key3': value3});
```

**Tip 66: Handle Migration in SQLite**
```dart
await openDatabase(
  'app.db',
  version: 2,
  onCreate: (db, version) => createTables(db),
  onUpgrade: (db, oldVersion, newVersion) => migrateTables(db, oldVersion),
);
```

**Tip 67: Consider ObjectBox for Performance**
```dart
// ObjectBox offers high performance for complex queries
// pubspec.yaml: objectbox: ^latest
```

**Tip 68: Always Close Boxes**
```dart
@override
void dispose() {
  Hive.close();
  super.dispose();
}
```

---

## Category 9: Networking Tips (8 tips)

**Tip 69: Use Dio for Advanced HTTP**
```dart
final dio = Dio(BaseOptions(
  baseUrl: 'https://api.example.com',
  connectTimeout: Duration(seconds: 10),
  receiveTimeout: Duration(seconds: 10),
));
```

**Tip 70: Interceptors for Auth & Logging**
```dart
dio.interceptors.add(InterceptorsWrapper(
  onRequest: (options, handler) {
    options.headers['Authorization'] = 'Bearer $token';
    handler.next(options);
  },
  onError: (error, handler) {
    if (error.response?.statusCode == 401) {
      // Refresh token logic
    }
    handler.next(error);
  },
));
```

**Tip 71: LogInterceptor Should Be Last**
```dart
dio.interceptors.add(authInterceptor);
dio.interceptors.add(cacheInterceptor);
dio.interceptors.add(LogInterceptor()); // Always last!
```

**Tip 72: FormData for File Uploads**
```dart
final formData = FormData.fromMap({
  'file': await MultipartFile.fromFile(filePath, filename: 'receipt.jpg'),
  'description': 'Invoice receipt',
});
await dio.post('/upload', data: formData);
```

**Tip 73: Retry Logic for Failures**
```dart
dio.interceptors.add(RetryInterceptor(
  dio: dio,
  retries: 3,
  retryDelays: [1.seconds, 2.seconds, 3.seconds],
));
```

**Tip 74: Cancel Requests on Dispose**
```dart
final _cancelToken = CancelToken();

@override
void dispose() {
  _cancelToken.cancel('Widget disposed');
  super.dispose();
}
```

**Tip 75: Use Separate Dio for Token Refresh**
```dart
// Avoid circular dependency in interceptors
final mainDio = Dio();
final tokenDio = Dio(); // Dedicated for refresh
```

**Tip 76: Response Caching**
```dart
dio.interceptors.add(DioCacheManager(
  CacheConfig(baseUrl: 'https://api.example.com'),
).interceptor);
```

---

## Category 10: Theming Tips (8 tips)

**Tip 77: Use Material Design 3**
```dart
MaterialApp(
  theme: ThemeData(
    useMaterial3: true, // Default since Flutter 3.16
    colorScheme: ColorScheme.fromSeed(seedColor: Colors.blue),
  ),
)
```

**Tip 78: ColorScheme.fromSeed for Harmonious Colors**
```dart
// Generates complete color palette from single seed
ThemeData(
  colorScheme: ColorScheme.fromSeed(
    seedColor: Color(0xFF2E7D32), // Your brand color
    brightness: Brightness.light,
  ),
)
```

**Tip 79: Theme Extensions for Custom Tokens**
```dart
class AppColors extends ThemeExtension<AppColors> {
  final Color success;
  final Color warning;

  AppColors({required this.success, required this.warning});

  @override
  ThemeExtension<AppColors> copyWith({Color? success, Color? warning}) {
    return AppColors(
      success: success ?? this.success,
      warning: warning ?? this.warning,
    );
  }

  @override
  ThemeExtension<AppColors> lerp(ThemeExtension<AppColors>? other, double t) {
    if (other is! AppColors) return this;
    return AppColors(
      success: Color.lerp(success, other.success, t)!,
      warning: Color.lerp(warning, other.warning, t)!,
    );
  }
}

// Usage
Theme.of(context).extension<AppColors>()!.success
```

**Tip 80: Dark Mode Support**
```dart
MaterialApp(
  theme: ThemeData.light(useMaterial3: true),
  darkTheme: ThemeData.dark(useMaterial3: true),
  themeMode: ThemeMode.system, // Follows system setting
)
```

**Tip 81: Use Theme.of(context) Not Hardcoded Colors**
```dart
// ❌ Bad
Text('Hello', style: TextStyle(color: Colors.blue))

// ✅ Good
Text('Hello', style: TextStyle(color: Theme.of(context).colorScheme.primary))
```

**Tip 82: copyWith() for Local Overrides**
```dart
Theme(
  data: Theme.of(context).copyWith(
    cardTheme: CardTheme(elevation: 8),
  ),
  child: MyCard(),
)
```

**Tip 83: TextTheme for Consistent Typography**
```dart
Text(
  'Heading',
  style: Theme.of(context).textTheme.headlineMedium,
)
```

**Tip 84: Component Themes for Consistent Widgets**
```dart
ThemeData(
  elevatedButtonTheme: ElevatedButtonThemeData(
    style: ElevatedButton.styleFrom(
      padding: EdgeInsets.symmetric(horizontal: 24, vertical: 12),
    ),
  ),
)
```

---

## Category 11: Accessibility Tips (8 tips)

**Tip 85: Use Semantics Widget**
```dart
Semantics(
  label: 'Add to cart button',
  button: true,
  child: CustomButton(),
)
```

**Tip 86: Keep Labels Concise**
```dart
// ❌ Bad: "Button to add this item to your list of favorites"
// ✅ Good: "Add to favorites"
```

**Tip 87: MergeSemantics for Grouped Content**
```dart
// Groups multiple Text widgets into one semantic node
MergeSemantics(
  child: Row(
    children: [
      Text('\$'),
      Text('29.99'),
    ],
  ),
)
```

**Tip 88: ExcludeSemantics for Decorative Content**
```dart
ExcludeSemantics(
  child: DecorativeIcon(), // Screen reader ignores this
)
```

**Tip 89: Test with TalkBack/VoiceOver**
- Enable TalkBack (Android) or VoiceOver (iOS)
- Navigate your app with screen reader
- Ensure all interactive elements are announced

**Tip 90: Minimum Touch Target Size**
```dart
// Minimum 48x48 dp for touch targets
SizedBox(
  width: 48,
  height: 48,
  child: IconButton(icon: Icon(Icons.add), onPressed: () {}),
)
```

**Tip 91: SemanticsRole for Widget Type**
```dart
Semantics(
  role: SemanticsRole.slider,
  child: CustomSlider(),
)
```

**Tip 92: showSemanticsDebugger for Testing**
```dart
MaterialApp(
  showSemanticsDebugger: true, // Visualizes semantic tree
)
```

---

## Category 12: DevTools & Debugging Tips (8 tips)

**Tip 93: Profile Mode for Performance Testing**
```bash
flutter run --profile  # Use this for accurate performance metrics
```

**Tip 94: DevTools Memory Tab**
- Track allocations, detect memory leaks
- Take snapshots before/after navigation
- Compare to find retained objects

**Tip 95: Timeline for Frame Analysis**
- Open janky (red) frames in timeline
- Check build/layout/paint times
- Identify rebuild hotspots

**Tip 96: Performance Overlay**
```dart
MaterialApp(
  showPerformanceOverlay: true, // Shows GPU/UI graphs
)
```

**Tip 97: debugProfileBuildsEnabled**
```dart
// Add timeline events for every Widget built
debugProfileBuildsEnabled = true;
```

**Tip 98: CPU Profiler for Hot Paths**
- Identify expensive functions
- View call stack and execution time
- Find main thread bottlenecks

**Tip 99: Disable Effects for Profiling**
```dart
// In DevTools, disable clipping/shadows to test impact
// If performance improves, reduce these effects in app
```

**Tip 100: Export/Import Performance Snapshots**
- Export snapshots for sharing with team
- Import to compare before/after optimization

---

## Category 13: Null Safety Tips (6 tips)

**Tip 101: Prefer Non-Nullable Types**
```dart
// ✅ Good: Non-nullable by default
String name = 'John';

// Use nullable only when truly optional
String? middleName;
```

**Tip 102: Use ! Sparingly**
```dart
// Only use when 100% certain value is non-null
final length = text!.length; // Use with caution
```

**Tip 103: late for Lazy Initialization**
```dart
late final ApiService apiService; // Initialized in initState

@override
void initState() {
  super.initState();
  apiService = ApiService(context);
}
```

**Tip 104: Leverage Type Promotion**
```dart
void process(String? value) {
  if (value != null) {
    // Compiler knows value is non-null here
    print(value.length); // No ! needed
  }
}
```

**Tip 105: ?? for Default Values**
```dart
final name = user.name ?? 'Unknown';
final count = response.count ?? 0;
```

**Tip 106: ??= for Null Assignment**
```dart
String? _cache;
String get value => _cache ??= expensiveComputation();
```

---

## Category 14: Error Handling Tips (6 tips)

**Tip 107: Catch Specific Exceptions**
```dart
try {
  await fetchData();
} on SocketException {
  // Network error
} on FormatException {
  // Parse error
} catch (e) {
  // Unknown error
}
```

**Tip 108: Result Pattern for Expected Failures**
```dart
sealed class Result<T> {}
class Success<T> extends Result<T> { final T data; Success(this.data); }
class Failure<T> extends Result<T> { final Exception error; Failure(this.error); }
```

**Tip 109: FlutterError.onError for Framework Errors**
```dart
FlutterError.onError = (details) {
  // Log to analytics
  FirebaseCrashlytics.instance.recordFlutterError(details);
};
```

**Tip 110: PlatformDispatcher for Async Errors**
```dart
PlatformDispatcher.instance.onError = (error, stack) {
  // Log uncaught async errors
  return true;
};
```

**Tip 111: Finally for Cleanup**
```dart
try {
  await openFile();
} finally {
  await closeFile(); // Always runs
}
```

**Tip 112: ErrorWidget.builder for Production**
```dart
ErrorWidget.builder = (details) {
  return CustomErrorWidget(); // User-friendly error screen
};
```

---

## Category 15: Internationalization Tips (6 tips)

**Tip 113: Use flutter_localizations**
```yaml
dependencies:
  flutter_localizations:
    sdk: flutter
  intl: any
```

**Tip 114: ARB Files for Translations**
```json
// lib/l10n/app_en.arb
{
  "welcome": "Welcome",
  "itemCount": "{count, plural, =0{No items} =1{1 item} other{{count} items}}"
}
```

**Tip 115: Configure localizationsDelegates**
```dart
MaterialApp(
  localizationsDelegates: AppLocalizations.localizationsDelegates,
  supportedLocales: AppLocalizations.supportedLocales,
)
```

**Tip 116: Dynamic Locale Switching**
```dart
// Store locale preference and update MaterialApp.locale
setState(() => _locale = Locale('nl', 'NL'));
```

**Tip 117: ICU Message Format for Plurals**
```arb
{
  "daysRemaining": "{days, plural, =0{Due today} =1{1 day left} other{{days} days left}}"
}
```

**Tip 118: RTL Support**
```dart
// Flutter handles RTL automatically for supported locales
// Use Directionality widget for manual control
Directionality(
  textDirection: TextDirection.rtl,
  child: MyWidget(),
)
```

---

## Category 16: Platform Channels Tips (6 tips)

**Tip 119: Unique Channel Names**
```dart
const channel = MethodChannel('com.myapp.invoices/sync');
```

**Tip 120: Call on Main Thread**
```dart
// Platform channel calls must be on main thread
// Flutter handles this, but be aware in native code
```

**Tip 121: Use Pigeon for Type Safety**
```dart
// Generates type-safe platform channel code
// pubspec.yaml: pigeon: ^latest
```

**Tip 122: Handle MissingPluginException**
```dart
try {
  await channel.invokeMethod('getData');
} on MissingPluginException {
  // Method not implemented on this platform
}
```

**Tip 123: EventChannel for Streams**
```dart
const eventChannel = EventChannel('com.myapp/events');
eventChannel.receiveBroadcastStream().listen((data) {
  // Handle continuous data from native
});
```

**Tip 124: Hot Reload Limitations**
```dart
// Native code changes require full restart
// Hot reload only works for Dart code
```

---

## Category 17: Architecture Tips (6 tips)

**Tip 125: Feature-First for Large Apps**
```
lib/features/
├── auth/
│   ├── models/
│   ├── screens/
│   └── providers/
├── invoices/
└── reports/
```

**Tip 126: Clean Architecture Layers**
```
lib/
├── domain/     # Business logic (no Flutter imports)
├── data/       # Repositories, API clients
├── presentation/ # UI widgets
```

**Tip 127: Repository Pattern**
```dart
abstract class InvoiceRepository {
  Future<List<Invoice>> getAll();
  Future<Invoice> getById(String id);
  Future<void> save(Invoice invoice);
}
```

**Tip 128: Use Cases for Business Logic**
```dart
class CreateInvoiceUseCase {
  final InvoiceRepository repository;

  Future<Invoice> execute(InvoiceData data) {
    // Business rules here
    return repository.save(Invoice.fromData(data));
  }
}
```

**Tip 129: Dependency Injection with GetIt**
```dart
final getIt = GetIt.instance;

void setupDependencies() {
  getIt.registerSingleton<ApiClient>(ApiClient());
  getIt.registerFactory<InvoiceRepository>(() => InvoiceRepositoryImpl(getIt()));
}
```

**Tip 130: Start Domain-First**
- Define entities and business rules first
- Add data layer implementation
- Build UI last

---

## Category 18: Memory Management Tips (8 tips)

**Tip 131: Always Dispose Controllers**
```dart
@override
void dispose() {
  _textController.dispose();
  _scrollController.dispose();
  _animationController.dispose();
  super.dispose();
}
```

**Tip 132: Cancel Stream Subscriptions**
```dart
StreamSubscription? _subscription;

@override
void initState() {
  _subscription = stream.listen((data) {});
}

@override
void dispose() {
  _subscription?.cancel();
  super.dispose();
}
```

**Tip 133: Check mounted Before setState**
```dart
Future<void> loadData() async {
  final data = await fetchData();
  if (mounted) {
    setState(() => _data = data);
  }
}
```

**Tip 134: Avoid Global Variables**
```dart
// ❌ Bad: Persists entire app lifecycle
Map<String, dynamic> globalCache = {};

// ✅ Good: Scoped to widget/provider lifecycle
```

**Tip 135: Use ListView.builder for Large Lists**
```dart
// Builds only visible items, recycles widgets
ListView.builder(itemCount: 10000, itemBuilder: ...)
```

**Tip 136: Resize Images in Memory**
```dart
Image.network(
  url,
  cacheWidth: 200, // Resize in memory cache
  cacheHeight: 200,
)
```

**Tip 137: DevTools Memory Tab for Leak Detection**
- Take snapshot before navigation
- Navigate away and back
- Take snapshot again
- Compare for retained objects

**Tip 138: Weak References for Caches**
```dart
final cache = Expando<ExpensiveObject>();
// Allows garbage collection when key is collected
```

---

## Category 19: Security Tips (8 tips)

**Tip 139: flutter_secure_storage for Sensitive Data**
```dart
final storage = FlutterSecureStorage();
await storage.write(key: 'token', value: token);
```

**Tip 140: Never Store Secrets in Code**
```dart
// ❌ Never hardcode
const apiKey = 'sk_live_abc123';

// ✅ Use environment variables or secure storage
final apiKey = await secureStorage.read(key: 'api_key');
```

**Tip 141: SSL Pinning**
```dart
// Prevent MITM attacks by pinning certificate
dio.httpClientAdapter = DefaultHttpClientAdapter()
  ..onHttpClientCreate = (client) {
    client.badCertificateCallback = (cert, host, port) {
      return cert.sha256 == expectedFingerprint;
    };
  };
```

**Tip 142: Obfuscate Release Builds**
```bash
flutter build apk --obfuscate --split-debug-info=./debug-info
```

**Tip 143: Biometric Authentication**
```dart
final auth = LocalAuthentication();
final authenticated = await auth.authenticate(
  localizedReason: 'Authenticate to access financial data',
);
```

**Tip 144: Prevent Screenshots**
```dart
// Android: FLAG_SECURE
// iOS: UITextField trick or native implementation
```

**Tip 145: Validate All User Input**
```dart
// Server-side validation is essential
// Client-side validation improves UX but is not security
```

**Tip 146: HTTPS Only**
```dart
// Configure network_security_config.xml (Android)
// Configure App Transport Security (iOS)
```

---

## Category 20: CI/CD Tips (6 tips)

**Tip 147: GitHub Actions for Flutter**
```yaml
name: Flutter CI
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: subosito/flutter-action@v2
      - run: flutter pub get
      - run: flutter test
```

**Tip 148: Codemagic for Mobile Builds**
```yaml
# codemagic.yaml
workflows:
  android-workflow:
    scripts:
      - flutter build apk --release
```

**Tip 149: Fastlane for App Store Deployment**
```ruby
# Fastfile
lane :deploy do
  build_app
  upload_to_app_store
end
```

**Tip 150: Cache Dependencies**
```yaml
- uses: actions/cache@v3
  with:
    path: ~/.pub-cache
    key: ${{ runner.os }}-pub-${{ hashFiles('**/pubspec.lock') }}
```

**Tip 151: Test Before Build**
```yaml
steps:
  - run: flutter analyze
  - run: flutter test
  - run: flutter build apk  # Only if tests pass
```

**Tip 152: Secrets Management**
```yaml
env:
  API_KEY: ${{ secrets.API_KEY }}
```

---

## Category 21: Isolates & Concurrency Tips (8 tips)

**Tip 153: Use compute() for Simple Tasks**
```dart
final result = await compute(expensiveFunction, inputData);
```

**Tip 154: Isolate.run for One-Off Tasks**
```dart
final result = await Isolate.run(() {
  return heavyComputation();
});
```

**Tip 155: When to Use Isolates**
- JSON parsing of large files
- Image processing
- Complex calculations
- Anything causing UI jank (>16ms)

**Tip 156: Minimize Data Transfer**
```dart
// Send only necessary data to isolate
// Large data transfers can be slow
```

**Tip 157: Return Small Results**
```dart
// ❌ Bad: Return huge list
return hugeList; // Marshalling is slow

// ✅ Good: Process in isolate, return summary
return ProcessedSummary(count: list.length, total: sum);
```

**Tip 158: Long-Lived Isolates with Ports**
```dart
final receivePort = ReceivePort();
await Isolate.spawn(workerFunction, receivePort.sendPort);

receivePort.listen((message) {
  // Handle messages from isolate
});
```

**Tip 159: Isolate Pool for High Concurrency**
```dart
// Use package like 'isolate' for worker pools
final pool = IsolatePool(4); // 4 workers
await pool.compute(task);
```

**Tip 160: Main Thread for UI Only**
- Never do heavy computation on main thread
- Use isolates for CPU-bound work
- Keep main thread free for 60fps rendering

---

## Quick Reference: When to Use What

| Scenario | Solution |
|----------|----------|
| Simple state | Provider + ChangeNotifier |
| Complex state | Riverpod or BLoC |
| Simple storage | SharedPreferences |
| Structured data | Hive CE or Drift |
| HTTP requests | Dio with interceptors |
| Navigation | GoRouter |
| Animations | AnimatedContainer (simple) or AnimationController (complex) |
| Heavy computation | Isolate.run or compute() |
| Testing | flutter_test + mocktail |
| CI/CD | GitHub Actions or Codemagic |

---

*Version 3.0.0 - Added 160+ tips across 21 categories covering state management, widget optimization, testing, animations, navigation, forms, async programming, local storage, networking, theming, accessibility, debugging, null safety, error handling, i18n, platform channels, architecture, memory management, security, CI/CD, and isolates*

---

## Flutter Rendering Architecture Deep Dive

Understanding Flutter's internal rendering architecture helps you optimize performance and debug rendering issues.

### The Three Trees: Widget, Element, and RenderObject

```
┌─────────────────────────────────────────────────────────────────┐
│                    FLUTTER THREE-TREE ARCHITECTURE              │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│   WIDGET TREE              ELEMENT TREE           RENDER TREE   │
│   (Immutable Config)       (Lifecycle Mgmt)       (Layout/Paint)│
│                                                                  │
│   ┌───────────┐           ┌───────────┐          ┌───────────┐ │
│   │  MyApp    │ ─creates─►│ MyAppElem │          │           │ │
│   │ (Widget)  │           │ (Element) │          │           │ │
│   └─────┬─────┘           └─────┬─────┘          │           │ │
│         │                       │                 │           │ │
│   ┌─────▼─────┐           ┌─────▼─────┐          │           │ │
│   │ Scaffold  │ ─creates─►│ScaffoldEl │─creates─►│ScaffoldBox│ │
│   │ (Widget)  │           │ (Element) │          │(RenderObj)│ │
│   └─────┬─────┘           └─────┬─────┘          └─────┬─────┘ │
│         │                       │                       │       │
│   ┌─────▼─────┐           ┌─────▼─────┐          ┌─────▼─────┐ │
│   │  Column   │ ─creates─►│ ColumnEl  │─creates─►│ RenderFlex│ │
│   │ (Widget)  │           │ (Element) │          │(RenderObj)│ │
│   └─────┬─────┘           └─────┬─────┘          └─────┬─────┘ │
│         │                       │                       │       │
│   ┌─────▼─────┐           ┌─────▼─────┐          ┌─────▼─────┐ │
│   │   Text    │ ─creates─►│  TextEl   │─creates─►│RenderPara │ │
│   │ (Widget)  │           │ (Element) │          │(RenderObj)│ │
│   └───────────┘           └───────────┘          └───────────┘ │
│                                                                  │
│   Rebuilt on        Long-lived,           Performs actual       │
│   every change      manages lifecycle     layout and painting   │
└─────────────────────────────────────────────────────────────────┘
```

**Widget Tree** (Immutable Configuration):
- Describes the UI declaratively
- Immutable: new tree built on every `setState()`
- Lightweight: can be recreated many times
- Contains configuration, not state

**Element Tree** (Lifecycle Management):
- Links Widgets to RenderObjects
- Long-lived: survives widget rebuilds
- Manages state (in StatefulElement)
- Decides whether to rebuild or reuse

**RenderObject Tree** (Layout & Painting):
- Actually does the work
- Layout: calculates sizes and positions
- Paint: draws pixels to screen
- Expensive to create, so reused

### Widget Rebuild vs Element Reuse

```dart
// When does Element get reused?
// Answer: When Widget type and key match

// Scenario 1: Same type, same key → REUSE
Column(children: [
  Text('Hello'),  // TextElement reused
  Text('World'),  // TextElement reused
])

// Scenario 2: Different type → REPLACE
// Before:
Container(child: Text('Hello'))
// After:
Card(child: Text('Hello'))
// ContainerElement destroyed, CardElement created

// Scenario 3: Same type, different position → MIGHT REUSE
// Flutter uses linear diffing, so:
Column(children: [
  if (showFirst) Text('First'),
  Text('Second'),
])
// When showFirst toggles, 'Second' element might be reused for 'First'

// Scenario 4: Use Key to force identity
Column(children: [
  if (showFirst) Text('First', key: ValueKey('first')),
  Text('Second', key: ValueKey('second')),
])
// Now Elements match by key, not position
```

### The Rendering Pipeline

```
┌─────────────────────────────────────────────────────────────────┐
│                    RENDERING PIPELINE                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  User Input / Timer / Animation                                 │
│         │                                                        │
│         ▼                                                        │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ 1. BUILD PHASE                                           │   │
│  │    • setState() marks element dirty                      │   │
│  │    • Dirty elements call build()                        │   │
│  │    • New widgets compared to old                        │   │
│  │    • Elements reused or replaced                        │   │
│  └──────────────────────────┬──────────────────────────────┘   │
│                             ▼                                    │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ 2. LAYOUT PHASE                                          │   │
│  │    • Parent passes constraints down                      │   │
│  │    • Child returns size up                              │   │
│  │    • Parent positions child                             │   │
│  │    • Only dirty subtrees re-layout                      │   │
│  └──────────────────────────┬──────────────────────────────┘   │
│                             ▼                                    │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ 3. COMPOSITING PHASE                                     │   │
│  │    • Build layer tree                                   │   │
│  │    • Assign clips, transforms, opacity                  │   │
│  │    • RepaintBoundary creates new layer                  │   │
│  └──────────────────────────┬──────────────────────────────┘   │
│                             ▼                                    │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ 4. PAINT PHASE                                           │   │
│  │    • Walk render tree, paint each object                │   │
│  │    • Draw to Canvas (Skia/Impeller)                     │   │
│  │    • Only repaint dirty layers                          │   │
│  └──────────────────────────┬──────────────────────────────┘   │
│                             ▼                                    │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ 5. RASTERIZATION (GPU)                                   │   │
│  │    • Convert vector commands to pixels                  │   │
│  │    • Upload to GPU                                      │   │
│  │    • Composite layers                                   │   │
│  │    • Display on screen                                  │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  Target: Complete all phases in <16.67ms for 60fps             │
│  Or <8.33ms for 120fps displays                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Layout Constraints System

```dart
// Parent passes constraints, child returns size
// This is the "box model" in Flutter

/*
  ┌─────────────────────────────────────────────────────────────┐
  │                    CONSTRAINT FLOW                           │
  ├─────────────────────────────────────────────────────────────┤
  │                                                              │
  │  Parent: "You must be between 100-300px wide"               │
  │                         │                                    │
  │      BoxConstraints(    │                                    │
  │        minWidth: 100,   ▼                                    │
  │        maxWidth: 300,   ┌────────────────┐                  │
  │        minHeight: 0,    │     Child      │                  │
  │        maxHeight: 500,  │                │                  │
  │      )                  │  "I'll be 200" │                  │
  │                         └───────┬────────┘                  │
  │                                 │                            │
  │  Child returns:                 │                            │
  │    Size(200, 50)  ◄─────────────┘                           │
  │                                                              │
  └─────────────────────────────────────────────────────────────┘
*/

// Tight vs Loose Constraints
BoxConstraints.tight(Size(100, 100))  // Child MUST be 100x100
BoxConstraints.loose(Size(100, 100))  // Child can be UP TO 100x100

// Common constraint patterns:
Container(width: 100, height: 100)  // Tight constraints
ConstrainedBox(constraints: BoxConstraints(maxWidth: 200))  // Loose upper bound
Expanded(child: ...)  // Fills available space
SizedBox.shrink()  // Zero size
SizedBox.expand()  // Infinite constraints
```

### RepaintBoundary for Performance

```dart
// RepaintBoundary creates a separate layer
// Repaints only this subtree, not parents

// When to use:
// 1. Frequently animating widgets
RepaintBoundary(
  child: AnimatedWidget(), // Only this repaints on animation
)

// 2. Complex static widgets
RepaintBoundary(
  child: ComplexChart(), // Don't repaint when parent rebuilds
)

// 3. Scrollable content
ListView.builder(
  itemBuilder: (context, index) {
    return RepaintBoundary(
      child: ListItem(items[index]),
    );
  },
)

// When NOT to use:
// - Simple widgets (overhead > savings)
// - Rarely changing widgets
// - Already isolated by other boundaries

// Debug repaint regions:
debugRepaintRainbowEnabled = true; // Shows repaint areas
```

### Keys Deep Dive

```dart
/*
  ┌─────────────────────────────────────────────────────────────┐
  │                    KEY TYPES                                  │
  ├─────────────────────────────────────────────────────────────┤
  │                                                              │
  │  ValueKey<T>    - Identity from value                       │
  │  ObjectKey      - Identity from object reference            │
  │  UniqueKey      - Always unique (forces rebuild)            │
  │  GlobalKey      - Unique across entire app                  │
  │                                                              │
  └─────────────────────────────────────────────────────────────┘
*/

// ValueKey: Use when you have a stable identifier
ListView.builder(
  itemBuilder: (context, index) {
    final item = items[index];
    return ListTile(
      key: ValueKey(item.id), // Stable identity
      title: Text(item.name),
    );
  },
)

// ObjectKey: Use when comparing object references
final item = Item(id: '1');
Container(key: ObjectKey(item)) // Different if item reference changes

// UniqueKey: Use when you want to force rebuild
RefreshIndicator(
  key: UniqueKey(), // New key = new widget = reset state
  onRefresh: () async {},
  child: ListView(),
)

// GlobalKey: Use sparingly, expensive
// Allows accessing state from anywhere
final formKey = GlobalKey<FormState>();

Form(
  key: formKey,
  child: ...,
)

// Later:
formKey.currentState!.validate();
```

### 25 Flutter Rendering Insights

1. **Widgets are cheap, RenderObjects are expensive** - Rebuilding widgets is fast because Elements cache RenderObjects

2. **const creates canonical instances** - `const Text('Hello')` returns same instance everywhere

3. **setState() only marks this Element dirty** - Not the whole tree, just this subtree

4. **Element.updateChild() is the diff algorithm** - Compares old and new widgets

5. **Layout is single-pass** - Parent→child constraints, child→parent size

6. **Intrinsic dimensions are expensive** - `IntrinsicWidth` measures child twice

7. **Slivers enable lazy rendering** - Only visible items are laid out and painted

8. **Opacity widget is expensive** - Use AnimatedOpacity or FadeTransition instead

9. **Clip widgets add layers** - `ClipRRect` creates compositing layer

10. **Transform doesn't affect layout** - Only affects paint, not hit testing by default

11. **saveLayer() is expensive** - Called by many decorations, creates GPU texture

12. **Text layout is cached** - Changing text content invalidates cache

13. **Images are decoded in background** - First frame may show placeholder

14. **LayoutBuilder adds one frame delay** - Child builds after parent layout

15. **GlobalKey is O(n) lookup** - Linear search through all keys

16. **Opacity near 0 or 1 is optimized** - Skips saveLayer for fully opaque/transparent

17. **Scrolling uses viewport culling** - Items outside viewport aren't painted

18. **AnimatedContainer interpolates internally** - Lerps between old and new values

19. **MediaQuery triggers rebuilds** - Wrapping with MediaQuery.removeViewInsets helps

20. **Theme.of() creates dependency** - Widget rebuilds on theme changes

21. **BuildContext is the Element** - `context` and Element are the same object

22. **markNeedsBuild() is synchronous** - Build happens on next frame

23. **markNeedsLayout() walks up tree** - Marks all ancestors as needing relayout

24. **Layer boundaries reduce repaint cost** - But increase memory

25. **Impeller replaces Skia** - New rendering engine, pre-compiled shaders

### Performance Debugging Tools

```dart
// Enable performance overlay
MaterialApp(
  showPerformanceOverlay: true,
)

// Profile builds
debugProfileBuildsEnabled = true;

// Check paint layers
debugPaintLayerBordersEnabled = true;

// Show repaint regions
debugRepaintRainbowEnabled = true;

// Dump render tree
debugDumpRenderTree();

// Dump layer tree
debugDumpLayerTree();

// Check semantics
debugDumpSemanticsTree();
```

---

**Version 3.1.0** - Added Flutter Rendering Architecture Deep Dive covering three-tree architecture, rendering pipeline, constraints system, RepaintBoundary, Keys, and 25 rendering insights
