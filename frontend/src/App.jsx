import React from 'react';
import { BrowserRouter as Router, Switch, Route, Redirect } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import ProtectedRoute from './components/common/ProtectedRoute';
import Login from './components/auth/Login';
import Register from './components/auth/Register';
import Dashboard from './pages/Dashboard';
import QuizPage from './pages/QuizPage';
import AdminPage from './pages/AdminPage';

function App() {
  return (
    <AuthProvider>
      <Router>
        <Switch>
          <Route path="/login" component={Login} />
          <Route path="/register" component={Register} />
          <ProtectedRoute path="/dashboard" component={Dashboard} />
          <ProtectedRoute path="/quiz/:sessionId" component={QuizPage} />
          <ProtectedRoute path="/admin" requireAdmin={true} component={AdminPage} />
          <Route exact path="/" render={() => <Redirect to="/dashboard" />} />
        </Switch>
      </Router>
    </AuthProvider>
  );
}

export default App;

