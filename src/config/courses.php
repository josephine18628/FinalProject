<?php
/**
 * Course and Textbook Definitions
 * CS3 Quiz Platform
 */

/**
 * Get all available courses with their textbooks
 * @return array Array of courses with their details
 */
function getCourses() {
    return [
        [
            'id' => 'hardware_systems',
            'name' => 'Hardware and Systems Fundamentals',
            'description' => 'Computer systems, digital design, and computer organization',
            'textbooks' => [
                'Computer Systems: A Programmer\'s Perspective by Randal E. Bryant and David R. O\'Hallaron',
                'Digital Design and Computer Architecture by David Harris',
                'Structured Computer Organization by Andrew S. Tanenbaum',
                'Fundamentals of Computer Organization and Design by P. Dandamudi'
            ]
        ],
        [
            'id' => 'web_technologies',
            'name' => 'Web Technologies',
            'description' => 'Modern web development, JavaScript, and web content management',
            'textbooks' => [
                'Eloquent JavaScript by Marijn Haverbeke',
                'Learning Web Design by Jennifer Niederst Robbins',
                'Getting Started with The Web by Shelley Powers',
                'Web Content Management: Systems, Features, and Best Practices by Deane Barker',
                'Fundamentals of Web Programming'
            ]
        ],
        [
            'id' => 'cpp_programming',
            'name' => 'Intermediate Computer Programming (C++)',
            'description' => 'Advanced C++ programming concepts and techniques',
            'textbooks' => [
                'Absolute C++ by Kenrick Mock and Walter Savitch',
                'C++ Programming by D. S. Malik',
                'C++ How to Program by Harvey Deitel and Paul Deitel'
            ]
        ],
        [
            'id' => 'algorithms',
            'name' => 'Algorithm Design and Analysis',
            'description' => 'Algorithm design techniques, complexity analysis, and optimization',
            'textbooks' => [
                'Introduction to Algorithms (CLRS) by Cormen, Leiserson, Rivest, and Stein',
                'Algorithm Design by Kleinberg and Tardos',
                'The Algorithm Design Manual by Skiena',
                'Algorithms by Sedgewick',
                'Introduction to the Design and Analysis of Algorithms by Levitin'
            ]
        ],
        [
            'id' => 'research_methods',
            'name' => 'Research Methods',
            'description' => 'Research methodology, data analysis, and academic writing',
            'textbooks' => [
                'Research Design by Creswell',
                'The Craft of Research by Booth, Colomb, and Williams',
                'Writing for Computer Science by Zobel',
                'A Research Companion for Digital Humanities by Embley and Nagy',
                'Experimental Design and Analysis by Seltman'
            ]
        ],
        [
            'id' => 'modeling_simulation',
            'name' => 'Introduction to Modeling and Simulation',
            'description' => 'Simulation techniques, modeling methodologies, and analysis',
            'textbooks' => [
                'Discrete-Event System Simulation by Banks, Carson, Nelson, and Nicol',
                'Simulation Modeling and Analysis by Law',
                'Modeling and Simulation Fundamentals by Sokolowski and Banks',
                'Systems Simulation by Shannon',
                'Simulation and the Monte Carlo Method by Rubinstein and Kroese'
            ]
        ],
        [
            'id' => 'software_engineering',
            'name' => 'Software Engineering',
            'description' => 'Software development lifecycle, design patterns, and best practices',
            'textbooks' => [
                'Code Complete: A Practical Handbook of Software Construction by Steve McConnell',
                'The Pragmatic Programmer: From Journeyman to Master by Andrew Hunt and David Thomas',
                'Software Engineering: Theory and Practice by Shari Lawrence Pfleeger and Joanne M. Atlee',
                'Clean Architecture: A Craftsman\'s Guide to Software Structure and Design by Robert C. Martin'
            ]
        ],
        [
            'id' => 'computer_architecture',
            'name' => 'Computer Architecture',
            'description' => 'Hardware-software interface, processor design, and system architecture',
            'textbooks' => [
                'Computer Organization and Design: The Hardware/Software Interface by David A. Patterson and John L. Hennessy (RISC-V Edition)',
                'Computer Architecture: A Quantitative Approach by Hennessy and Patterson'
            ]
        ],
        [
            'id' => 'operating_systems',
            'name' => 'Operating Systems',
            'description' => 'OS concepts, process management, memory management, and file systems',
            'textbooks' => [
                'Operating System Concepts by Silberschatz, Galvin, and Gagne',
                'Modern Operating Systems by Andrew S. Tanenbaum'
            ]
        ],
        [
            'id' => 'database_systems',
            'name' => 'Database Systems',
            'description' => 'Database design, SQL, normalization, and transaction management',
            'textbooks' => [
                'Database System Concepts by Silberschatz, Korth, and Sudarshan',
                'Fundamentals of Database Systems by Elmasri and Navathe'
            ]
        ],
        [
            'id' => 'computer_networks',
            'name' => 'Computer Networks',
            'description' => 'Network protocols, architectures, and communication systems',
            'textbooks' => [
                'Computer Networks by Andrew S. Tanenbaum',
                'Computer Networking: A Top-Down Approach by Kurose and Ross'
            ]
        ]
    ];
}

/**
 * Get course by ID
 * @param string $courseId Course identifier
 * @return array|null Course details or null if not found
 */
function getCourseById($courseId) {
    $courses = getCourses();
    foreach ($courses as $course) {
        if ($course['id'] === $courseId) {
            return $course;
        }
    }
    return null;
}

/**
 * Get course textbooks
 * @param string $courseId Course identifier
 * @return array Array of textbook names
 */
function getCourseTextbooks($courseId) {
    $course = getCourseById($courseId);
    return $course ? $course['textbooks'] : [];
}
?>

