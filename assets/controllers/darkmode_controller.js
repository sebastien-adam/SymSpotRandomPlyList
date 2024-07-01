// Import the Controller class from Stimulus
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["dark", "light"];

    connect() {
      // Check if a theme is saved in localStorage and apply it
      const savedTheme = localStorage.getItem("theme");
      if (savedTheme) {
        document.documentElement.setAttribute("data-theme", savedTheme);
        this.hideIcon(savedTheme);
    }
    }

  // Define a method to toggle dark mode
  toggle() {
    // Check the current theme and toggle it
    const currentTheme = document.documentElement.getAttribute("data-theme");
    const newTheme = currentTheme === "dark" ? "light" : "dark";

    this.hideIcon(newTheme);
    // Update the `data-theme` attribute on the <html> element
    document.documentElement.setAttribute("data-theme", newTheme);

    // Save the current mode to localStorage
    localStorage.setItem("theme", newTheme);
  }

  hideIcon(theme) {
    if (theme === "dark") {
      this.darkTarget.hidden = true;
      this.lightTarget.hidden = false;
    }
    else {
      this.darkTarget.hidden = false;
      this.lightTarget.hidden = true;
    }
  }

}
