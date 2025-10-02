// Mock product data for demo
export const mockProducts = [
  {
    id: 1,
    name: "DentWizard Professional Polo",
    description: "Premium cotton polo with embroidered DentWizard logo. Perfect for client meetings and professional events.",
    price: 45.99,
    category: "Polos",
    image: "https://via.placeholder.com/400x400/1976d2/ffffff?text=Professional+Polo",
    colors: ["Navy", "White", "Black", "Gray"],
    sizes: ["S", "M", "L", "XL", "2XL"],
    inStock: true,
    featured: true,
    badge: "Best Seller"
  },
  {
    id: 2,
    name: "DentWizard Tech Jacket",
    description: "Water-resistant soft shell jacket with fleece lining. Features zippered pockets and adjustable cuffs.",
    price: 89.99,
    category: "Outerwear",
    image: "https://via.placeholder.com/400x400/424242/ffffff?text=Tech+Jacket",
    colors: ["Black", "Navy", "Charcoal"],
    sizes: ["S", "M", "L", "XL", "2XL", "3XL"],
    inStock: true,
    featured: true,
    badge: "New Arrival"
  },
  {
    id: 3,
    name: "DentWizard Performance T-Shirt",
    description: "Moisture-wicking athletic t-shirt with UV protection. Ideal for outdoor work and training.",
    price: 24.99,
    category: "T-Shirts",
    image: "https://via.placeholder.com/400x400/ff9800/ffffff?text=Performance+Tee",
    colors: ["Blue", "Gray", "Black", "White"],
    sizes: ["XS", "S", "M", "L", "XL", "2XL"],
    inStock: true,
    featured: false
  },
  {
    id: 4,
    name: "DentWizard Executive Dress Shirt",
    description: "Wrinkle-resistant dress shirt with button-down collar. Professional appearance for important meetings.",
    price: 59.99,
    category: "Dress Shirts",
    image: "https://via.placeholder.com/400x400/e0e0e0/333333?text=Dress+Shirt",
    colors: ["White", "Light Blue", "Gray"],
    sizes: ["14", "15", "15.5", "16", "16.5", "17", "17.5"],
    inStock: true,
    featured: false
  },
  {
    id: 5,
    name: "DentWizard Fleece Hoodie",
    description: "Comfortable fleece hoodie with kangaroo pocket. Features embroidered logo on chest.",
    price: 54.99,
    category: "Hoodies",
    image: "https://via.placeholder.com/400x400/4caf50/ffffff?text=Fleece+Hoodie",
    colors: ["Navy", "Gray", "Black"],
    sizes: ["S", "M", "L", "XL", "2XL"],
    inStock: true,
    featured: true,
    badge: "Popular"
  },
  {
    id: 6,
    name: "DentWizard Baseball Cap",
    description: "Structured 6-panel cap with adjustable strap. Embroidered logo on front.",
    price: 19.99,
    category: "Accessories",
    image: "https://via.placeholder.com/400x400/2196f3/ffffff?text=Baseball+Cap",
    colors: ["Navy", "Black", "White"],
    sizes: ["One Size"],
    inStock: true,
    featured: false
  },
  {
    id: 7,
    name: "DentWizard Work Pants",
    description: "Durable work pants with reinforced knees and multiple pockets for tools.",
    price: 64.99,
    category: "Pants",
    image: "https://via.placeholder.com/400x400/795548/ffffff?text=Work+Pants",
    colors: ["Khaki", "Navy", "Black"],
    sizes: ["28", "30", "32", "34", "36", "38", "40"],
    inStock: true,
    featured: false
  },
  {
    id: 8,
    name: "DentWizard Laptop Backpack",
    description: "Professional backpack with padded laptop compartment. Multiple organization pockets.",
    price: 79.99,
    category: "Accessories",
    image: "https://via.placeholder.com/400x400/607d8b/ffffff?text=Laptop+Backpack",
    colors: ["Black", "Gray"],
    sizes: ["One Size"],
    inStock: true,
    featured: false
  },
  {
    id: 9,
    name: "DentWizard Ladies Polo",
    description: "Tailored fit polo designed for women. Moisture-wicking fabric with stretch.",
    price: 42.99,
    category: "Polos",
    image: "https://via.placeholder.com/400x400/e91e63/ffffff?text=Ladies+Polo",
    colors: ["Navy", "White", "Light Blue"],
    sizes: ["XS", "S", "M", "L", "XL"],
    inStock: true,
    featured: false
  },
  {
    id: 10,
    name: "DentWizard Safety Vest",
    description: "High-visibility safety vest with reflective strips. ANSI Class 2 compliant.",
    price: 29.99,
    category: "Safety",
    image: "https://via.placeholder.com/400x400/ffeb3b/333333?text=Safety+Vest",
    colors: ["Hi-Vis Yellow", "Hi-Vis Orange"],
    sizes: ["S/M", "L/XL", "2XL/3XL"],
    inStock: true,
    featured: false
  },
  {
    id: 11,
    name: "DentWizard Quarter-Zip Pullover",
    description: "Lightweight quarter-zip pullover. Perfect for layering in cooler weather.",
    price: 49.99,
    category: "Pullovers",
    image: "https://via.placeholder.com/400x400/9c27b0/ffffff?text=Quarter+Zip",
    colors: ["Navy", "Gray", "Black"],
    sizes: ["S", "M", "L", "XL", "2XL"],
    inStock: true,
    featured: true
  },
  {
    id: 12,
    name: "DentWizard Travel Mug",
    description: "Insulated stainless steel travel mug with DentWizard logo. Keeps drinks hot or cold.",
    price: 14.99,
    category: "Accessories",
    image: "https://via.placeholder.com/400x400/00bcd4/ffffff?text=Travel+Mug",
    colors: ["Silver", "Black", "Blue"],
    sizes: ["16oz"],
    inStock: true,
    featured: false
  }
];

// Mock categories
export const mockCategories = [
  { name: "All Products", count: 12 },
  { name: "Polos", count: 2 },
  { name: "T-Shirts", count: 1 },
  { name: "Outerwear", count: 1 },
  { name: "Hoodies", count: 1 },
  { name: "Dress Shirts", count: 1 },
  { name: "Pants", count: 1 },
  { name: "Pullovers", count: 1 },
  { name: "Accessories", count: 3 },
  { name: "Safety", count: 1 }
];

// Mock user data
export const mockUser = {
  firstName: "John",
  lastName: "Demo",
  email: "john.demo@dentwizard.com",
  department: "IT Department",
  employeeId: "DW12345",
  budget: {
    allocated: 500.00,
    used: 125.50,
    remaining: 374.50
  }
};

// Mock orders
export const mockOrders = [
  {
    id: "ORD-2024-001",
    date: "2024-01-15",
    status: "delivered",
    total: 135.97,
    items: [
      { name: "DentWizard Professional Polo", quantity: 2, price: 45.99 },
      { name: "DentWizard Baseball Cap", quantity: 2, price: 19.99 }
    ]
  },
  {
    id: "ORD-2024-002", 
    date: "2024-02-20",
    status: "shipped",
    total: 89.99,
    items: [
      { name: "DentWizard Tech Jacket", quantity: 1, price: 89.99 }
    ]
  }
];