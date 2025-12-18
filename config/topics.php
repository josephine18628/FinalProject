<?php
/**
 * Course Topics Configuration
 * CS3 Quiz Platform
 * Topics organized by course based on standard textbook chapters
 */

/**
 * Get all topics for a specific course
 * @param string $courseId Course identifier
 * @return array Array of topics with categories
 */
function getCourseTopics($courseId) {
    $allTopics = [
        'hardware_systems' => [
            'categories' => [
                'Computer Organization' => [
                    'Data Representation and Number Systems',
                    'Boolean Algebra and Logic Gates',
                    'Digital Components and Circuits',
                    'Computer Arithmetic'
                ],
                'Processor Architecture' => [
                    'CPU Organization and Design',
                    'Instruction Set Architecture',
                    'Pipelining and Parallelism',
                    'RISC vs CISC Architectures'
                ],
                'Memory Systems' => [
                    'Memory Hierarchy',
                    'Cache Memory',
                    'Virtual Memory',
                    'Secondary Storage'
                ],
                'I/O Systems' => [
                    'Input/Output Organization',
                    'Interrupts and DMA',
                    'Bus Systems',
                    'Peripheral Devices'
                ]
            ]
        ],
        
        'web_technologies' => [
            'categories' => [
                'HTML & CSS' => [
                    'HTML5 Structure and Semantics',
                    'CSS Styling and Layout',
                    'Responsive Design and Flexbox/Grid',
                    'CSS Preprocessors and Frameworks'
                ],
                'JavaScript Fundamentals' => [
                    'Variables, Data Types, and Operators',
                    'Functions and Scope',
                    'Objects and Prototypes',
                    'ES6+ Features (Arrow Functions, Classes, Modules)'
                ],
                'Advanced JavaScript' => [
                    'DOM Manipulation',
                    'Event Handling',
                    'Asynchronous JavaScript (Promises, Async/Await)',
                    'AJAX and Fetch API'
                ],
                'Web Development Concepts' => [
                    'HTTP Protocol and REST APIs',
                    'Web Security (XSS, CSRF, CORS)',
                    'Web Performance Optimization',
                    'Progressive Web Apps (PWAs)'
                ],
                'Frameworks & Tools' => [
                    'JavaScript Frameworks Overview',
                    'Package Managers (npm, yarn)',
                    'Build Tools (Webpack, Vite)',
                    'Version Control (Git)'
                ]
            ]
        ],
        
        'cpp_programming' => [
            'categories' => [
                'C++ Fundamentals' => [
                    'Data Types and Variables',
                    'Control Structures (if, loops, switch)',
                    'Functions and Parameter Passing',
                    'Arrays and Strings'
                ],
                'Object-Oriented Programming' => [
                    'Classes and Objects',
                    'Constructors and Destructors',
                    'Inheritance and Polymorphism',
                    'Encapsulation and Abstraction'
                ],
                'Advanced OOP' => [
                    'Operator Overloading',
                    'Friend Functions and Classes',
                    'Virtual Functions and Abstract Classes',
                    'Multiple Inheritance'
                ],
                'Memory Management' => [
                    'Pointers and References',
                    'Dynamic Memory Allocation',
                    'Memory Leaks and Smart Pointers',
                    'Copy Constructors and Assignment Operators'
                ],
                'STL & Advanced Topics' => [
                    'Standard Template Library (STL)',
                    'Templates and Generic Programming',
                    'Exception Handling',
                    'File I/O and Streams'
                ]
            ]
        ],
        
        'algorithms' => [
            'categories' => [
                'Algorithm Basics' => [
                    'Algorithm Analysis and Big-O Notation',
                    'Asymptotic Notation (O, Ω, Θ)',
                    'Recurrence Relations',
                    'Master Theorem'
                ],
                'Sorting & Searching' => [
                    'Sorting Algorithms (Quick, Merge, Heap)',
                    'Searching Algorithms (Binary Search, Interpolation)',
                    'Lower Bounds for Sorting',
                    'External Sorting'
                ],
                'Data Structures' => [
                    'Arrays, Lists, and Stacks',
                    'Trees (Binary, BST, AVL, Red-Black)',
                    'Heaps and Priority Queues',
                    'Hash Tables and Hashing'
                ],
                'Graph Algorithms' => [
                    'Graph Representations',
                    'Depth-First and Breadth-First Search',
                    'Shortest Path (Dijkstra, Bellman-Ford)',
                    'Minimum Spanning Trees (Kruskal, Prim)'
                ],
                'Algorithm Design Techniques' => [
                    'Divide and Conquer',
                    'Greedy Algorithms',
                    'Dynamic Programming',
                    'Backtracking'
                ],
                'Advanced Topics' => [
                    'NP-Completeness',
                    'Approximation Algorithms',
                    'String Matching Algorithms',
                    'Computational Geometry'
                ]
            ]
        ],
        
        'research_methods' => [
            'categories' => [
                'Research Fundamentals' => [
                    'Types of Research (Qualitative, Quantitative)',
                    'Research Problem and Questions',
                    'Literature Review',
                    'Research Ethics'
                ],
                'Research Design' => [
                    'Experimental Design',
                    'Survey Design',
                    'Case Study Methods',
                    'Action Research'
                ],
                'Data Collection' => [
                    'Sampling Techniques',
                    'Interviews and Questionnaires',
                    'Observation Methods',
                    'Data Recording'
                ],
                'Data Analysis' => [
                    'Descriptive Statistics',
                    'Inferential Statistics',
                    'Hypothesis Testing',
                    'Qualitative Data Analysis'
                ],
                'Academic Writing' => [
                    'Research Paper Structure',
                    'Citation and Referencing',
                    'Technical Writing',
                    'Presenting Research'
                ]
            ]
        ],
        
        'modeling_simulation' => [
            'categories' => [
                'Simulation Basics' => [
                    'Introduction to Modeling and Simulation',
                    'System Concepts and Models',
                    'Discrete vs Continuous Simulation',
                    'Simulation Software'
                ],
                'Discrete-Event Simulation' => [
                    'Event Scheduling',
                    'Queuing Systems',
                    'Entity-Activity Diagrams',
                    'Process-Oriented Simulation'
                ],
                'Random Number Generation' => [
                    'Pseudo-Random Numbers',
                    'Random Variate Generation',
                    'Testing Random Numbers',
                    'Monte Carlo Methods'
                ],
                'Statistical Analysis' => [
                    'Input Modeling',
                    'Output Analysis',
                    'Confidence Intervals',
                    'Validation and Verification'
                ],
                'Advanced Topics' => [
                    'Continuous Simulation',
                    'Agent-Based Modeling',
                    'System Dynamics',
                    'Optimization in Simulation'
                ]
            ]
        ],
        
        'software_engineering' => [
            'categories' => [
                'Software Development Process' => [
                    'Software Development Life Cycle (SDLC)',
                    'Waterfall, Agile, and Scrum',
                    'Requirements Engineering',
                    'Project Management'
                ],
                'Software Design' => [
                    'Design Principles (SOLID, DRY, KISS)',
                    'Design Patterns (Creational, Structural, Behavioral)',
                    'UML Diagrams',
                    'Architecture Patterns (MVC, Layered, Microservices)'
                ],
                'Software Quality' => [
                    'Testing Strategies (Unit, Integration, System)',
                    'Test-Driven Development (TDD)',
                    'Code Review and Quality Metrics',
                    'Continuous Integration/Deployment (CI/CD)'
                ],
                'Software Maintenance' => [
                    'Code Refactoring',
                    'Version Control',
                    'Documentation',
                    'Legacy System Management'
                ],
                'Professional Practice' => [
                    'Software Engineering Ethics',
                    'Team Collaboration',
                    'Software Licensing',
                    'Risk Management'
                ]
            ]
        ],
        
        'computer_architecture' => [
            'categories' => [
                'Instruction Set Architecture' => [
                    'RISC-V ISA',
                    'Instruction Formats',
                    'Addressing Modes',
                    'Assembly Language Programming'
                ],
                'Processor Design' => [
                    'Datapath and Control',
                    'Single-Cycle and Multi-Cycle Processors',
                    'Pipelining',
                    'Hazards and Forwarding'
                ],
                'Memory Hierarchy' => [
                    'Cache Design and Organization',
                    'Cache Performance',
                    'Virtual Memory',
                    'TLBs and Page Tables'
                ],
                'Parallelism' => [
                    'Instruction-Level Parallelism (ILP)',
                    'Thread-Level Parallelism (TLP)',
                    'Multicore Processors',
                    'GPU Architecture'
                ],
                'Advanced Topics' => [
                    'Branch Prediction',
                    'Out-of-Order Execution',
                    'Memory Consistency',
                    'Performance Evaluation'
                ]
            ]
        ],
        
        'operating_systems' => [
            'categories' => [
                'OS Fundamentals' => [
                    'Operating System Concepts',
                    'System Calls and API',
                    'OS Structure (Monolithic, Microkernel)',
                    'Virtualization'
                ],
                'Process Management' => [
                    'Processes and Threads',
                    'CPU Scheduling Algorithms',
                    'Process Synchronization',
                    'Deadlocks'
                ],
                'Memory Management' => [
                    'Swapping and Paging',
                    'Segmentation',
                    'Virtual Memory Management',
                    'Page Replacement Algorithms'
                ],
                'Storage Management' => [
                    'File Systems',
                    'File System Implementation',
                    'Disk Scheduling',
                    'RAID Systems'
                ],
                'I/O & Security' => [
                    'I/O Systems and Devices',
                    'Protection and Security',
                    'Access Control',
                    'Cryptography in OS'
                ]
            ]
        ],
        
        'database_systems' => [
            'categories' => [
                'Database Fundamentals' => [
                    'Database Concepts and Architecture',
                    'Data Models (Relational, NoSQL)',
                    'ER Modeling',
                    'Relational Model'
                ],
                'SQL' => [
                    'SQL Basics (SELECT, INSERT, UPDATE, DELETE)',
                    'Joins and Subqueries',
                    'Aggregate Functions and GROUP BY',
                    'Views and Indexes'
                ],
                'Database Design' => [
                    'Normalization (1NF to BCNF)',
                    'Functional Dependencies',
                    'Schema Refinement',
                    'Database Design Process'
                ],
                'Transactions' => [
                    'ACID Properties',
                    'Concurrency Control',
                    'Locking Protocols',
                    'Recovery Techniques'
                ],
                'Advanced Topics' => [
                    'Query Optimization',
                    'Distributed Databases',
                    'NoSQL Databases',
                    'Big Data and Data Warehousing'
                ]
            ]
        ],
        
        'computer_networks' => [
            'categories' => [
                'Network Fundamentals' => [
                    'Network Models (OSI, TCP/IP)',
                    'Network Types (LAN, WAN, MAN)',
                    'Network Topologies',
                    'Transmission Media'
                ],
                'Physical & Data Link Layer' => [
                    'Encoding and Modulation',
                    'Error Detection and Correction',
                    'MAC Protocols',
                    'Ethernet and WiFi'
                ],
                'Network Layer' => [
                    'IP Addressing and Subnetting',
                    'Routing Algorithms',
                    'IPv4 and IPv6',
                    'NAT and DHCP'
                ],
                'Transport Layer' => [
                    'TCP and UDP',
                    'Reliable Data Transfer',
                    'Flow Control and Congestion Control',
                    'Socket Programming'
                ],
                'Application Layer' => [
                    'HTTP and HTTPS',
                    'DNS',
                    'Email Protocols (SMTP, POP3, IMAP)',
                    'FTP and SSH'
                ],
                'Network Security' => [
                    'Cryptography Basics',
                    'SSL/TLS',
                    'Firewalls and VPNs',
                    'Network Attacks and Defense'
                ]
            ]
        ]
    ];
    
    return $allTopics[$courseId] ?? ['categories' => []];
}

/**
 * Get flat list of all topics for a course
 * @param string $courseId Course identifier
 * @return array Simple array of topic names
 */
function getCourseTopicsList($courseId) {
    $courseTopics = getCourseTopics($courseId);
    $topicsList = [];
    
    foreach ($courseTopics['categories'] as $category => $topics) {
        foreach ($topics as $topic) {
            $topicsList[] = $topic;
        }
    }
    
    return $topicsList;
}

/**
 * Get topics organized by category for display
 * @param string $courseId Course identifier
 * @return array Categories with topics
 */
function getOrganizedTopics($courseId) {
    $courseTopics = getCourseTopics($courseId);
    return $courseTopics['categories'] ?? [];
}
?>

