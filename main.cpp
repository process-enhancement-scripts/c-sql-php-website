#include <iostream>
#include <vector>
#include <string>
#include <random>
#include <map>
#include <limits> // Required for numeric_limits
#include <cctype> // Required for std::toupper

// --- SQL Database Schema (Conceptual) ---
// This section outlines the SQL tables you would need.
// You would create these tables in your actual SQL database (e.g., MySQL, PostgreSQL, SQLite).
/*
CREATE TABLE characters (
    character_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    -- image_url VARCHAR(255) -- Will add later as per request
);

CREATE TABLE tier_assignments (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    character_id INT,
    tier_rank CHAR(1) NOT NULL, -- S, A, B, C, D, E, F
    assignment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(character_id)
);

-- Example data for characters table:
INSERT INTO characters (name) VALUES ('Eren Yeager');
INSERT INTO characters (name) VALUES ('Levi Ackerman');
INSERT INTO characters (name) VALUES ('Mikasa Ackerman');
INSERT INTO characters (name) VALUES ('Gojo Satoru');
INSERT INTO characters (name) VALUES ('Sukuna');
INSERT INTO characters (name) VALUES ('Monkey D. Luffy');
INSERT INTO characters (name) VALUES ('Roronoa Zoro');
INSERT INTO characters (name) VALUES ('Nami');
INSERT INTO characters (name) VALUES ('Naruto Uzumaki');
INSERT INTO characters (name) VALUES ('Sasuke Uchiha');
*/

// --- C++ Application Simulation ---

// Represents an anime character from the database
struct Character {
    int id;
    std::string name;
    // std::string imageUrl; // Placeholder for future
};

// Mock Database Manager to simulate interactions
class DatabaseManager {
private:
    std::vector<Character> characters;
    std::map<int, char> tierAssignments; // character_id -> tier_rank
    std::random_device rd;
    std::mt19937 gen;

public:
    DatabaseManager() : gen(rd()) {
        // Simulate loading characters from a database
        characters.push_back({1, "Eren Yeager"});
        characters.push_back({2, "Levi Ackerman"});
        characters.push_back({3, "Mikasa Ackerman"});
        characters.push_back({4, "Gojo Satoru"});
        characters.push_back({5, "Sukuna"});
        characters.push_back({6, "Monkey D. Luffy"});
        characters.push_back({7, "Roronoa Zoro"});
        characters.push_back({8, "Nami"});
        characters.push_back({9, "Naruto Uzumaki"});
        characters.push_back({10, "Sasuke Uchiha"});
    }

    // Returns a random unranked character
    Character getRandomUnrankedCharacter() {
        std::vector<Character> unranked;
        for (const auto& ch : characters) {
            if (tierAssignments.find(ch.id) == tierAssignments.end()) {
                unranked.push_back(ch);
            }
        }

        if (unranked.empty()) {
            return {-1, "No more unranked characters!"}; // Sentinel value
        }

        std::uniform_int_distribution<> distrib(0, unranked.size() - 1);
        return unranked[distrib(gen)];
    }

    // Assigns a character to a tier
    void assignCharacterToTier(int characterId, char tier) {
        tierAssignments[characterId] = tier;
        std::cout << "Assigned character ID " << characterId << " to tier " << tier << std::endl;
        // In a real app, this would involve an SQL INSERT/UPDATE statement
        // e.g., INSERT INTO tier_assignments (character_id, tier_rank) VALUES (?, ?);
    }

    // Display current tier list (for simulation purposes)
    void displayTierList() const {
        std::cout << "\n--- Current Tier List ---\n";
        std::map<char, std::vector<std::string>> categorizedTiers;
        for (const auto& entry : tierAssignments) {
            int charId = entry.first;
            char tier = entry.second;
            // Find character name by ID
            for (const auto& ch : characters) {
                if (ch.id == charId) {
                    categorizedTiers[tier].push_back(ch.name);
                    break;
                }
            }
        }

        std::vector<char> tierOrder = {'S', 'A', 'B', 'C', 'D', 'E', 'F'};
        for (char t : tierOrder) {
            if (categorizedTiers.count(t)) {
                std::cout << t << " Tier: ";
                for (const auto& name : categorizedTiers.at(t)) {
                    std::cout << name << ", ";
                }
                std::cout << "\n";
            }
        }
        std::cout << "------------------------\n";
    }
};

int main() {
    DatabaseManager dbManager;

    std::cout << "Welcome to the Anime Character Tier List Maker!\n";
    std::cout << "Available Tiers: S, A, B, C, D, E, F\n";

    while (true) {
        Character currentCharacter = dbManager.getRandomUnrankedCharacter();

        if (currentCharacter.id == -1) {
            std::cout << "\nAll characters have been ranked! Thank you!\n";
            break;
        }

        std::cout << "\nCharacter: " << currentCharacter.name << "\n";
        // Image placeholder: In a real GUI, you'd load currentCharacter.imageUrl here
        std::cout << "[Image Placeholder]\n";

        char tierInput;
        while (true) {
            std::cout << "Enter Tier (S, A, B, C, D, E, F) or 'q' to quit: ";
            std::cin >> tierInput;
            tierInput = static_cast<char>(std::toupper(static_cast<unsigned char>(tierInput))); // Convert to uppercase safely

            if (tierInput == 'Q') {
                std::cout << "Exiting Tier List Maker.\n";
                return 0;
            }

            if (std::string("SABCDEF").find(tierInput) != std::string::npos) {
                dbManager.assignCharacterToTier(currentCharacter.id, tierInput);
                break;
            } else {
                std::cout << "Invalid tier. Please enter S, A, B, C, D, E, or F.\n";
                std::cin.clear(); // Clear error flags
                std::cin.ignore(std::numeric_limits<std::streamsize>::max(), '\n'); // Discard invalid input
            }
        }
        dbManager.displayTierList();
    }

    return 0;
}
